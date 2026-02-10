const http = require('http');

const data = JSON.stringify({
  imei: '123456789012345',
  latitude: 40.7128,
  longitude: -74.0060,
  speed: 60,
  heading: 90,
  timestamp: Date.now()
});

const options = {
  hostname: 'localhost',
  port: 3000,
  path: '/api/device/push',
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Content-Length': data.length
  }
};

const req = http.request(options, (res) => {
  console.log(`StatusCode: ${res.statusCode}`);
  
  res.on('data', (d) => {
    process.stdout.write(d);
  });
});

req.on('error', (error) => {
  console.error(error);
});

req.write(data);
req.end();
