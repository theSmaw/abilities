/*
description: A test case to see what is the optimum amount of PNG24 to load at any one time
type: benchmark
warnings: This test contains lots of flashing images and may not be suitable for those who suffer from epilepsy
*/

helpers.loadScript('assets/js/vendor/q.js');

var baseURL = 'assets/img/';
var lastImg;

function loadImage(ext)
{
    var dfd = new Q.defer();
    var img = new Image();
    var el = document.createElement('img');

    img.onload = function()
    {
        el.src = img.src;

        if (lastImg)
        {
            dumpArea.insertBefore(el, lastImg);
        }
        else
        {
            dumpArea.appendChild(el);
        }

        lastImg = el;

        dfd.resolve();
    }

    img.src = baseURL + ext;

    return dfd.promise;
}

function loadImages(ext, num, dfd)
{
    var promises = [ ];

    for (var i = num - 1; i >= 0; i--)
    {
        promises.push(
            loadImage(ext)
        );
    }

    Q
    .all(promises)
    .done(function()
    {
        dfd.resolve();
    });
}

suite
.on('cycle', function()
{
    // clear the dump area
    dumpArea.innerHTML = '';

    // null last image
    lastImg = null;
})
.add('dom-image-load-number#png24x10', {
    defer: true,
    fn: function(dfd)
    {
        loadImages('png24.png', 10, dfd);
    }
})
.add('dom-image-load-number#png24x50', {
    defer: true,
    fn: function(dfd)
    {
        loadImages('png24.png', 50, dfd);
    }
})
.add('dom-image-load-number#png24x100', {
    defer: true,
    fn: function(dfd)
    {
        loadImages('png24.png', 100, dfd);
    }
});

// wait for q before we run
var runInterval = setInterval(function()
{
    if ('Q' in window)
    {
        if ('defer' in Q)
        {
            clearTimeout(runInterval);

            suite.run({
                async: true
            });
        }
    }
}, 500);