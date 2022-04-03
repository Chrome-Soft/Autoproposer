// script forever js -hez
const { exec } = require('child_process');
exec("php artisan queue:work database --tries=3", function (err) {
    if (err) console.log('ERROR:', err);
    else console.log('queue started...');
});