const crypto = require('crypto');
const uuid = require('uuid');
const express = require('express');
const mysql = require('mysql');
const bodyParser = require('body-parser');
var bcrypt = require('bcrypt');

var con = mysql.createConnection({
  host: 'localhost',
  user: 'pvp_mysql',
  password: 'K233LARAVEL',
  database: 'pvp',
});

var app = express();
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

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
