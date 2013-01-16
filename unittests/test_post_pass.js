// make up a device id
var deviceId = 'casper-' + (new Date()).getTime();

casper
.start().open(url, {
    method: 'post',
    data: {
        'results[0][name]': 'a_test',
        'results[0][description]': 'This is a test, woop',
        'results[0][value]': 1,
        'results[1][name]': 'another_test',
        'results[1][description]': 'This is another test, woop',
        'results[1][value]': 'foo-bar-foo'
    },
    headers: {
        'X_REQUEST_DEVICE': deviceId
    }
})
.then(function()
{
    this.test.assertTextExists(deviceId, 'Check that we get the device ID back');
    this.test.assertHttpStatus(200, 'Check that we get a 200 status');
});

casper.run(function()
{
    this.test.done();
});