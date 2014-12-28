function str_replace (search, replace, subject, count) {
    var i = 0, j = 0, temp = '', repl = '', sl = 0, fl = 0,
            f = [].concat(search),
            r = [].concat(replace),
            s = subject,
            ra = r instanceof Array, sa = s instanceof Array;
    s = [].concat(s);
    if (count) {
        this.window[count] = 0;
    }
 
    for (i=0, sl=s.length; i < sl; i++) {
        if (s[i] === '') {
            continue;
        }
        for (j=0, fl=f.length; j < fl; j++) {
            temp = s[i]+'';
            repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
            s[i] = (temp).split(f[j]).join(repl);
            if (count && s[i] !== temp) {
                this.window[count] += (temp.length-s[i].length)/f[j].length;}
        }
    }
    return sa ? s : s[0];
}

// FIRST DECLARE/INITIATE OUR VARIABLES...
var args, url, page;
var resourceWait  = 300, // how many milliseconds to wait after a resource has finished loading (just a little time to make sure all zthis resource's elements are rendered on the page)
    maxRenderWait = 10000; // how many milliseconds to wait for page render. Current: 10 seconds

// get the command arguments...
args = require('system').args;
if (args.length < 1) {
    console.error('Invalid arguments');
    phantom.exit();
}
// here we can add arguments to the URL. This is just an example for now...
url = args[1];// + '?dt=' + encodeURIComponent(args[1]);
var screenshot = args[2];

// Start PhantomJS
var page          = require('webpage').create(),
    count         = 0,
    forcedRenderTimeout,
    renderTimeout;

//==============================================================================
// HANDLE USER AGENT & REFERER
//==============================================================================
//console.log('The default user agent is ' + page.settings.userAgent);
// but I can change it...
page.settings.userAgent = args[3];
// This is how you set other header variables
page.customHeaders = {'Referer': args[4]};

//==============================================================================
// SET THE SCREEN SIZE...
//==============================================================================
page.viewportSize = { width: 1280, height : 1024 };

//==============================================================================
// MAIN PAGE PROCESSING FUNCTION!
//==============================================================================
function doRender() {
	//------------------------------------------------
	// EVALUATE THE MAIN PAGE AND RETURN ITS CONTENTS
	//------------------------------------------------
    var page_contents = page.evaluate(function() {
            // If I need jQuery to run on the page I can inject it...
            /*
            if ( typeof(jQuery) == "undefined" )
            {
                // Force Load
                page.injectJs('http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js');
            }
            */
      return document.documentElement.outerHTML;
      // I could also do: return document.getElementById('secguard').textContent;
	  // document.documentElement.outerHTML doesn't return doctype. But I can use document.doctype to get it and glue the two together.
    });
    console.log(page_contents);
	page.clipRect = { left: 0, top: 0, width: 1280, height: 1024 };
	page.render( screenshot );
	//----------
	// AND EXIT
	//----------
    phantom.exit();
}

//==============================================================================
// CHECK WHEN A NEW RESOURCE IS REQUESTED
// when yes, clear the rendering delay, to start a new one
//==============================================================================
page.onResourceRequested = function (req) {
    count += 1;
    //console.log('> ' + req.id + ' - ' + req.url);
    clearTimeout(renderTimeout);
};

//==============================================================================
// CHECK WHEN A NEW RESOURCE IS RECEIVED, WAIT FOR IT TO LOAD, THEN RENDER IT
// start a new process when a new resource is received (css, js, image, etc.)
//==============================================================================
page.onResourceReceived = function (res) {
    if (!res.stage || res.stage === 'end') {
        count -= 1;
        //console.log(res.id + ' ' + res.status + ' - ' + res.url);
        if (count === 0) {
            renderTimeout = setTimeout(doRender, resourceWait);
        }
    }
};

//==============================================================================
// OPEN THE GIVEN URL
// Here is the Start line...
//==============================================================================
page.open(url, function (status) {
    if (status !== "success") {
        console.log('Unable to load url');
        phantom.exit();
    } else {
        forcedRenderTimeout = setTimeout(function () {
            //console.log(count);
            doRender();
        }, maxRenderWait);
    }
});
