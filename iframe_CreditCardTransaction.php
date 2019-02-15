<?php 
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Run Credit Card Transaction Transaction Example</title>
    <style>
        .main {
            display: block;
            height: 900px;
            width: 400px;

            margin: 10px;
            padding: 15px;
            text-align: center;
        }

        iframe {
            width: 100%;
            height: 95%;
            border: 0;
            display: none;
            min-height: 290px;
        }

        #loader {
            display: block;
        }

        .iframe-container {
            border: 2px solid black;
            text-align: center;
            height: 750px;
        }
    </style>
</head>
<body> 
<div class="main">

    <span>Your Website</span>

    <br/><br/>

    <div id="errors-container" style="display:  none, margin-top: 10px; margin-bottom: 10px; "></div>

    <div id="forms-container">
        <div class="iframe-container">
            <span>Our Iframe</span>
            <iframe id="iframe1" ></iframe>
        </div>

        <br/><br/>

        <div id="loader">Loading Form...</div>

        <form id="myForm">
            <button id="submitme">Submit Form</button>
        </form>
    </div>

</div>

<script src="./jquery-3.3.1.min.js"></script>
<script>
    const myForm = window.document.getElementById('myForm');
    
    const iframeUrl = '<?php echo $_POST["iframeurl"]; ?>';
    const iframeDomain = iframeUrl.match(/^http(s?):\/\/.*?(?=\/)/)[0];
    const iframesrc = '<?php echo $_POST["token"]; ?>';
    const order_id = '<?php echo $_POST["orderid"]; ?>';
    const phpurl = '<?php echo $_POST["phpurl"]; ?>';
    const successurl = '<?php echo $_POST["redirecturl_success"]; ?>';
    const failurl = '<?php echo $_POST["redirecturl_fail"]; ?>';
    window.addEventListener('message', function messageListener(event) {
        if (event.origin === iframeDomain) {
            console.log('received message', event.data);
            if (event.data.event === 'loaded') {
                window.document.getElementById('iframe1').style.display = 'block';
                window.document.getElementById('loader').style.display = 'none';
            }
            if (event.data.event === 'processed') {
                console.log('processed transaction', event.data.data);
                var jsonStr = JSON.stringify(event.data.data, null, 1);
                window.document.getElementById('forms-container').innerHTML = '<p>Successfully Processed Credit Card Transaction.</p><code><br/>' + jsonStr + '</code>';
                recallphp();
            }
        }
    });

    function recallphp()
    {
        $.post(phpurl, {'cmsnexio_action':'transcomplete','order_id':order_id}, function(data){
             
             // show the response
             //alert('getdata: ' + data);
             if(data == 'complete')
             {
                window.location.replace(successurl);
             }
             else
             {
                window.location.replace(failurl);
             }
              
         }).fail(function() {
          
             // just in case posting your form failed
             alert( "Posting failed." );
             window.location.replace(failurl);
         });
    }

    function LoadiFrame()
    {
        window.document.getElementById('iframe1').src = iframesrc;
        myForm.addEventListener('submit', function processPayment(event) {
            event.preventDefault();
            iframe1.contentWindow.postMessage('posted', iframesrc);
            return false;
        });

    }
    
    LoadiFrame();
</script>
</body>
</html>
