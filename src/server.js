var express = require('express');
var mysql = require('mysql2');
var app = express();
const con = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: 'root',
  database: 'appointment'
});
const port = 3000;

// estabilish db connection
con.connect(function(err) {
  if (err) throw err;
  console.log("Connected!");
});

// set the view engine to ejs
app.set('view engine', 'ejs');
app.use(express.static("public"));
app.use(express.json());
// use res.render to load up an ejs view file

// index page
app.get('/', function(req, res) {
  res.render('index', {data: 10});
});

app.get('/calendar', function(req, res) {
  res.render('calendar');
})

app.get('/api/get_services', function(req, res) {
  if (req.query.service != null) {
    con.query("SELECT * FROM Service WHERE idService = ?", [req.query.service], function (error, results, fields) {
      if (error) throw error;
      res.json(results);
    });
  } else {
    con.query("SELECT * FROM Service", function (error, results, fields) {
      if (error) throw error;
      res.json(results);
    });
  }
})
app.listen(port);

console.log('Server is listening on port ' + port);