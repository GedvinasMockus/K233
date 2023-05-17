require('dotenv').config();
const express = require('express');
const mysql = require('mysql');
const bodyParser = require('body-parser');
const { base64encode, base64decode } = require('nodejs-base64');
const mqtt = require('mqtt');
const Buffer = require('buffer').Buffer;
const bcrypt = require('bcrypt');
const fs = require('fs');
const crypto = require('crypto');
const { v4: uuidv4 } = require('uuid');
const mime = require('mime-types');
const path = require('path');
const axios = require('axios');
var FormData = require('form-data');

const app = express();
app.use(bodyParser.json({ limit: '50mb' }));
app.use(bodyParser.urlencoded({ limit: '50mb', extended: true }));

const con = mysql.createConnection({
  host: process.env.MYSQL_HOST,
  user: process.env.MYSQL_USER,
  password: process.env.MYSQL_PASSWORD,
  database: process.env.MYSQL_DATABASE,
});

const mqttClientId = 'mqttjs_' + Math.random().toString(16).substr(2, 8);

const mqttOptions = {
  port: 8883,
  host: process.env.MQTT_HOST,
  clientId: mqttClientId,
  username: process.env.MQTT_USERNAME,
  password: process.env.MQTT_PASSWORD,
  keepalive: 60,
  reconnectPeriod: 1000,
  protocol: 'mqtts',
};

var topicDown;
const client = mqtt.connect(mqttOptions);

client.on('connect', function () {
  console.log('Client connected to TTN');
  client.subscribe('#');
});

client.on('error', function (err) {
  console.log(err);
});

function processTTNData(getDataFromTTN, decoded) {
  const applicationID =
    getDataFromTTN.end_device_ids.application_ids.application_id;
  const endDeviceID = getDataFromTTN.end_device_ids.device_id;
  topicDown = `v3/${applicationID}@ttn/devices/${endDeviceID}/down/push`;
  var data = {};
  const uuid = `${decoded.substr(0, 8)}-${decoded.substr(
    8,
    4
  )}-${decoded.substr(12, 4)}-${decoded.substr(16, 4)}-${decoded.substr(
    20,
    12
  )}`;
  data.uuid = uuid;
  data.distance = decoded.substr(32);
  data.parking = getDataFromTTN.uplink_message.f_port;
  return data;
}

function createMessageToSend(payload, getDataFromTTN) {
  const messageToSend = {
    downlinks: [
      {
        f_port: getDataFromTTN.uplink_message.f_port,
        frm_payload: Buffer.from(JSON.stringify(payload)).toString('base64'),
      },
    ],
  };
  const buffer = Buffer.from(JSON.stringify(messageToSend));
  client.publish(topicDown, buffer);
}

client.on('message', function (topic, message) {
  try {
    const getDataFromTTN = JSON.parse(message);
    const rawData = getDataFromTTN.uplink_message.frm_payload;
    const decoded = base64decode(rawData);
    if (decoded.length < 30) {
      const data = JSON.parse(decoded);
      if ('Working' in data) {
        console.log(`Working: ${data.Working}`);
      }
    } else {
      var data = processTTNData(getDataFromTTN, decoded);
      console.log(data);
      if (data.distance <= 500) {
        con.query(
          'SELECT * FROM user where uuid=?',
          [data.uuid],
          function (err, result, fields) {
            con.on('error', function (err) {
              console.log('[MySQL ERROR]', err);
            });
            if (result && result.length) {
              con.query(
                'SELECT `reservation`.`id`, `reservation`.`date_from`, `reservation`.`date_until`, `reservation`.`is_inside`, `parking_space`.`fk_Parking_lotid`, `parking_space`.`space_number`' +
                  'FROM `reservation` LEFT JOIN `parking_space` ON `reservation`.`fk_Parking_spaceid`=`parking_space`.`id`' +
                  'WHERE `fk_Userid`=? AND `fk_Parking_lotid`=? AND CURRENT_TIMESTAMP()>=`date_from` AND CURRENT_TIMESTAMP()<=`date_until` LIMIT 1',
                [result[0].id, data.parking],
                function (err, DBresult, fields) {
                  if (err) {
                    console.log('[MySQL ERROR]', err);
                  } else {
                    if (DBresult.length == 1 && DBresult[0].is_inside == 0) {
                      createMessageToSend(
                        { status: '1', nr: DBresult[0].space_number },
                        getDataFromTTN
                      );
                      console.log('Įleidžiama');
                      writeReservation(DBresult[0].id);
                      console.table(DBresult);
                    } else {
                      createMessageToSend({ status: '0' }, getDataFromTTN);
                      console.log('Rezervacija nerasta');
                    }
                  }
                }
              );
              console.table(data);
            } else {
              createMessageToSend({ status: '2' }, getDataFromTTN);
            }
          }
        );
      } else {
        createMessageToSend({ status: '3' }, getDataFromTTN);
        console.log('Atstumas perdidelis');
      }
    }
  } catch (err) {}
});

function writeReservation(id) {
  const md5Hash = crypto.createHash('md5').update(id.toString()).digest('hex');
  fs.writeFile(
    'insideParking/' + md5Hash,
    `${id},${new Date().getTime()}`,
    (err) => {
      if (err) {
        console.log('Klaida kuriant rezervacijos failą:', err);
      }
    }
  );
}

function checkReservation() {
  fs.readdir('insideParking', (err, files) => {
    if (err) {
      console.log('Klaida skaitant rezervacijų aplankalą:', err);
      return;
    }
    files.forEach((file) => {
      fs.readFile('insideParking/' + file, 'utf8', (err, data) => {
        if (err) {
          console.log('Klaida skaitant rezervacijos failą:', err);
          return;
        }
        const [id, timeAdded] = data.split(',');
        const currentTime = new Date().getTime();
        if (currentTime - parseInt(timeAdded) >= 90 * 1000) {
          fs.unlink('insideParking/' + file, (err) => {
            if (err) {
              console.log('Klaida trinant failą:', err);
            }
          });
          con.query(
            'UPDATE `reservation` SET `is_inside` = 1 WHERE `id` = ?',
            [id],
            (err, result) => {
              if (err) {
                console.log('[MySQL ERROR]', err);
              } else {
                console.log(`Rezervacija id ${id} atnaujinta.`);
              }
            }
          );
        }
      });
    });
  });
}

setInterval(checkReservation, 10000);

app.post('/login/', (req, resData, next) => {
  var post_data = req.body;
  var user_password = post_data.password;
  var email = post_data.email;

  con.query(
    'SELECT id, uuid, email, password FROM user where email=?',
    [email],
    function (err, result, fields) {
      con.on('error', function (err) {
        console.log('[MySQL ERROR]', err);
      });
      if (result && result.length) {
        var encrypt = result[0].password;
        var hash = encrypt.replace(/^\$2y(.+)$/i, '$2a$1');
        bcrypt.compare(user_password, hash, function (err, res) {
          if (res == true) {
            delete result[0].password;
            resData.end(JSON.stringify([result[0]]));
          } else {
            resData.end(JSON.stringify('Slaptažodis neteisingas!'));
          }
        });
      } else {
        resData.json('Vartotojas nerastas!');
      }
    }
  );
});

app.post('/openBarrier/', (req, resData, next) => {
  var post_data = req.body;
  var uuid = post_data.uuid;
  var email = post_data.email;
  con.query(
    'SELECT id FROM user where uuid=? and email=?',
    [uuid, email],
    function (err, result, fields) {
      con.on('error', function (err) {
        console.log('[MySQL ERROR]', err);
      });
      if (result && result.length) {
        con.query(
          'SELECT `reservation`.`date_from`, `reservation`.`date_until`, `reservation`.`is_inside`, `parking_space`.`space_number`, `parking_lot`.`parking_name`, `parking_lot`.`photo_path`,' +
            '`parking_space`.`x1`, `parking_space`.`y1`, `parking_space`.`x2`, `parking_space`.`y2`, `parking_space`.`x3`, `parking_space`.`y3`, `parking_space`.`x4`, `parking_space`.`y4`' +
            'FROM `reservation` LEFT JOIN `parking_space` ON `reservation`.`fk_Parking_spaceid`=`parking_space`.`id`' +
            'LEFT JOIN `parking_lot` ON `parking_space`.`fk_Parking_lotid`=`parking_lot`.`id`' +
            'WHERE `fk_Userid`=? AND CURRENT_TIMESTAMP()>=`date_from` AND CURRENT_TIMESTAMP()<=`date_until` LIMIT 1',
          [result[0].id],
          function (err, DBresult, fields) {
            if (err) {
              console.log('[MySQL ERROR]', err);
            } else {
              if (DBresult.length == 1) {
                const coordinates = [];
                for (let i = 1; i <= 4; i++) {
                  const x = DBresult[0][`x${i}`];
                  const y = DBresult[0][`y${i}`];
                  delete DBresult[0][`x${i}`];
                  delete DBresult[0][`y${i}`];
                  coordinates.push(`${x},${y}`);
                }
                DBresult[0].coordinates = coordinates;
                resData.json(DBresult);
              } else {
                resData.json('Rezervacija nerasta');
              }
            }
          }
        );
      } else {
        resData.json('Vartotojo informacija nerasta!');
      }
    }
  );
});

app.post('/sendReport/', (req, resData, next) => {
  const { image, description, email } = req.body;
  const decodedImage = Buffer.from(image, 'base64');
  const extension = mime.extension('image/png');
  if (!extension) {
    return resData.status(400).json('Blogas paveiklsiuko formatas!');
  }
  const filename = `${uuidv4()}.${extension}`;
  const imagePath = path.join(__dirname, 'image', filename);
  fs.writeFile(imagePath, decodedImage, (err) => {
    if (err) {
      return resData.status(500).json('Klaida išsaugant paveiksliuką!');
    }
    const config = {
      headers: {
        'api-key': process.env.API_KEY,
        'Content-Type': 'multipart/form-data',
      },
    };
    const formData = new FormData();
    formData.append('description', description);
    formData.append('email', email);
    formData.append('parking_lot', '1');
    formData.append('image', fs.createReadStream(imagePath));
    axios
      .post('http://78.62.39.220/api/uploadReport', formData, config)
      .then((response) => {
        fs.unlink(imagePath, (unlinkErr) => {
          if (unlinkErr) {
            console.error(unlinkErr);
          }
          resData.status(200).json(response.data);
        });
      })
      .catch((error) => {
        console.error(error);
      });
  });
});

app.get('/parkingLot/', (req, resData, next) => {
  con.query(
    "SELECT `id`, `parking_name`, CONCAT(`city`, ', ', `street`, ' ', `street_number`) AS `address` FROM `parking_lot`",
    function (error, results, fields) {
      resData.json(results);
    }
  );
});

app.listen(3000, () => {
  console.log('Servas on');
});
