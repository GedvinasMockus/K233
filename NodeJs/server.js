const express = require('express');
const mysql = require('mysql');
const bodyParser = require('body-parser');
const { base64encode, base64decode } = require('nodejs-base64');
const mqtt = require('mqtt');
const Buffer = require('buffer').Buffer;
const bcrypt = require('bcrypt');

const app = express();
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

const con = mysql.createConnection({
  host: 'localhost',
  user: 'pvp_mysql',
  password: 'K233LARAVEL',
  database: 'pvp',
});

const mqttHost = 'eu1.cloud.thethings.network';
const mqttUsername = 'parking-system-ktu@ttn';
const mqttPassword =
  'NNSXS.RU57MNKXUNSHCYRRXUYOMAWGJ5K7E3CIQNGKNMQ.7EK3MG3I5AGALHVBFBNJ662FFXX3VIK2OCSXNVCQYJAQY3ZIEXKA';
const mqttClientId = 'mqttjs_' + Math.random().toString(16).substr(2, 8);

const mqttOptions = {
  port: 8883,
  host: mqttHost,
  clientId: mqttClientId,
  username: mqttUsername,
  password: mqttPassword,
  keepalive: 60,
  reconnectPeriod: 1000,
  protocol: 'mqtts',
};

const client = mqtt.connect(mqttOptions);

// MQTT setup
client.on('connect', function () {
  console.log('Client connected to TTN');
  client.subscribe('#');
});

client.on('error', function (err) {
  console.log(err);
});

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
      const applicationID =
        getDataFromTTN.end_device_ids.application_ids.application_id;
      const endDeviceID = getDataFromTTN.end_device_ids.device_id;
      const topic = `v3/${applicationID}@ttn/devices/${endDeviceID}/down/push`;
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
      console.log(data);
      if (data.distance <= 100) {
        con.query(
          'SELECT * FROM user where uuid=?',
          [data.uuid],
          function (err, result, fields) {
            con.on('error', function (err) {
              console.log('[MySQL ERROR]', err);
            });
            if (result && result.length) {
              console.log('Įleidžiama');
              console.table(data);
              const messageToSend = {
                downlinks: [
                  {
                    f_port: getDataFromTTN.uplink_message.f_port,
                    frm_payload: Buffer.from(
                      JSON.stringify({ status: 'open' })
                    ).toString('base64'),
                  },
                ],
              };
              const buffer = Buffer.from(JSON.stringify(messageToSend));
              client.publish(topic, buffer);
            } else {
              const messageToSend = {
                downlinks: [
                  {
                    f_port: getDataFromTTN.uplink_message.f_port,
                    frm_payload: Buffer.from(
                      JSON.stringify({ status: 'error' })
                    ).toString('base64'),
                  },
                ],
              };
              const buffer = Buffer.from(JSON.stringify(messageToSend));

              client.publish(topic, buffer);
              console.log('UUID nerastas!');
            }
          }
        );
      } else {
        const messageToSend = {
          downlinks: [
            {
              f_port: getDataFromTTN.uplink_message.f_port,
              frm_payload: Buffer.from(
                JSON.stringify({ status: 'closed' })
              ).toString('base64'),
            },
          ],
        };
        const buffer = Buffer.from(JSON.stringify(messageToSend));

        client.publish(topic, buffer);
        console.log('Atstumas perdidelis');
      }
    }
  } catch (err) {
    console.log('Nėra info');
  }
});

app.post('/login/', (req, resData, next) => {
  var post_data = req.body;
  var user_password = post_data.password;
  var email = post_data.email;

  con.query(
    'SELECT * FROM user where email=?',
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

app.listen(3000, () => {
  console.log('Servas on');
});
