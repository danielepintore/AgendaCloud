var express = require('express');
var app = express();
const port = 3000;

// set the view engine to ejs
app.set('view engine', 'ejs');
app.use(express.static("public"));
// use res.render to load up an ejs view file

// index page
app.get('/', function(req, res) {
  res.render('index', {data: 10});
});

app.get('/calendar', function(req, res) {
  res.render('calendar', {data: 2});
})

app.listen(port);

console.log('Server is listening on port ' + port);