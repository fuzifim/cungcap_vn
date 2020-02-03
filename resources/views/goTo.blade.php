<?php
$urlRoot = str_replace('https://fast.accesstrade.com.vn/deep_link/4883621352765649682?url=','',$url);
?>
<!DOCTYPE html>
<html>
<head>
    <title>{!! 'Url Redirect to '.str_replace('https://fast.accesstrade.com.vn/deep_link/4883621352765649682?url=','',$url) !!}</title>
    <meta charset="utf-8">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta name="keywords" content="">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
    <div class="card-body">
        <div class="form-group">
            <div class="alert alert-dark">
                This URL (<strong><span id="linkUrl"></span></strong>) is not belong to Cung Cap. Net, if you want to continue, please click bellow button to redirect to
            </div>
        </div>
        <div class="form-group">
            <a class="btn btn-success btn-block" id="linkContinue" href="">Click here to continue <span id="timeLeft"></span></a>
        </div>
    </div>
</div>
<script type="application/ld-json" id="json-url">{!!json_encode($url)!!}</script>
<script type="text/javascript">
    var redirUrl=jQuery.parseJSON(jQuery("#json-url").html());

    jQuery(document).ready(function(){
        jQuery("#linkContinue").attr("href",redirUrl);
        jQuery("#linkUrl").html('{!! $urlRoot !!}');
    });
    var count = 1;
    setInterval(function(){
        document.getElementById('timeLeft').innerHTML = count;
        if (count == 0) {
            window.location = redirUrl;
        }
        count--;
    },1000);
</script>
</body>
</html>
