var express = require('express');
var mysql = require('mysql2');
var app = express();

const con = mysql.createConnection({
  host: 'localhost', //TODO: change this in production
  user: 'root',
  password: 'root',
  database: 'agenda-cloud'
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
    con.query("SELECT * FROM Servizio WHERE id = ?", [req.query.service], function (error, results, fields) {
      if (error) throw error;
      res.json(results);
    });
  } else {
    con.query("SELECT * FROM Servizio", function (error, results, fields) {
      if (error) throw error;
      res.json(results);
    });
  }
})

app.get('/api/get_slots', function(req, res){
  // if the requests data is set
  if (req.query.date != null && req.query.serviceId != null) {
    // get service info
    con.query("SELECT Durata, OraInizio, OraFine FROM Servizio WHERE id = ?;", [req.query.serviceId], function (error, results, fields) {
      if (error) throw error;
      // ottengo gli slot occupati
      con.query("SELECT OraInizio, OraFine FROM Appuntamento WHERE Appuntamento.Data = \"2022-01-31\"", function (error, results, fields) {
        if (error) throw error;
        res.json(results);
        console.log(results[0].Durata)
        res.json(results);
      });
    });
  }
})

app.listen(port);

console.log('Server is listening on port ' + port);