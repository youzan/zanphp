<?php

return <<<EOT

<!DOCTYPE html>
<!--[if IEMobile 7 ]>    <html class="no-js iem7"> <![endif]-->
<!--[if (gt IEMobile 7)|!(IEMobile)]><!--> <html class="no-js"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta name="keywords" content="有赞,微信商城,粉丝营销,微信商城运营" />
    <meta name="description" content="有赞是帮助商家在微信上搭建微信商城的平台，提供店铺、商品、订单、物流、消息和客户的管理模块，同时还提供丰富的营销应用和活动插件。" />
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta http-equiv="cleartype" content="on">

    <link rel="icon" href="https://su.yzcdn.cn/v2/image/yz_fc.ico" />

    <title>出错了，请稍后再试！- 有赞</title>

    <!-- ▼Page CSS -->
    <style>
    html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{margin:0;padding:0;border:0;font:inherit;font-size:100%;vertical-align:baseline}html{line-height:1}ol,ul{list-style:none}table{border-collapse:collapse;border-spacing:0}caption,th,td{font-weight:normal;vertical-align:middle}q,blockquote{quotes:none}q:before,q:after,blockquote:before,blockquote:after{content:"";content:none}a img{border:none}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section,summary{display:block}html{width:100%;height:100%}body{color:#000;background:#fff;font-size:14px;-webkit-font-smoothing:antialiased;font-smoothing:antialiased;font-family:Helvetica, "STHeiti STXihei", "Microsoft JhengHei", "Microsoft YaHei", Tohoma, Arial;width:100%;height:100%;overflow-x:hidden;overflow-y:hidden}.container{padding-top:88px;min-width:320px}.circle-viewport{background-color:#d6f0ff;box-shadow:inset 2px 3px 5px rgba(0,0,0,0.1);margin:0 auto 20px;position:relative;width:280px;height:280px;-webkit-border-radius:140px;-moz-border-radius:140px;-ms-border-radius:140px;-o-border-radius:140px;border-radius:140px;overflow:hidden}.error-msg{text-align:center;color:#333;font-size:16px;line-height:1.5em}.mask{position:absolute;top:0px;left:0px;z-index:1000;width:280px;height:280px;background:transparent url('https://su.yzcdn.cn/v2/image/wap/trex/mask@1x_1416453746141.png') center center no-repeat}.sun{position:absolute;top:42px;left:80px;width:36px;height:36px;border-radius:30px;background-color:#f1ca0d}.mountain-1{position:absolute;bottom:50px;left:-21px;height:0;z-index:20;border-style:solid;border-width:0px 160px 150px 90px;border-color:transparent transparent #4dd7cc transparent}.mountain-2{position:absolute;bottom:48px;left:36px;height:0;z-index:25;border-style:solid;border-width:0px 165px 210px 150px;border-color:transparent transparent #98e2da transparent}.ground{position:absolute;z-index:10;z-index:10;bottom:-15px;left:-20px;display:block;width:300px;height:80px;background:#fbfcd5 url('https://su.yzcdn.cn/v2/image/wap/trex/ground@1x_1416453746141.png') center center no-repeat}.trex{position:absolute;z-index:50;bottom:12px;left:65px;display:block;width:120px;height:129px;background:url('https://su.yzcdn.cn/v2/image/wap/trex/trex@1x_1416453746141.png') center center no-repeat}.page{position:absolute;z-index:40;bottom:70px;left:176px;display:block;width:24px;height:33px;background:url('https://su.yzcdn.cn/v2/image/wap/trex/page@1x_1416453746141.png') center center no-repeat}@media only screen and (-webkit-min-device-pixel-ratio: 1.5), only screen and (min--moz-device-pixel-ratio: 1.5), only screen and (-o-min-device-pixel-ratio: 3 / 2), only screen and (min-device-pixel-ratio: 1.5){.mask{background-image:url('https://su.yzcdn.cn/v2/image/wap/trex/mask@2x_1416453746141.png');background-size:300px 300px}.trex{background-image:url('https://su.yzcdn.cn/v2/image/wap/trex/trex@2x_1416453746141.png');background-size:120px 129px}.page{background-image:url('https://su.yzcdn.cn/v2/image/wap/trex/page@2x_1416453746141.png');background-size:24px 33px}.ground{background-image:url('https://su.yzcdn.cn/v2/image/wap/trex/ground@2x_1416453746141.png');background-size:192px 24px}}
    </style>
    <!-- ▲Page CSS -->

</head>

<body>
    <div class="container">
        <div class="circle-viewport">
            <div data-parallaxify-range="5" class="para sun"></div>
            <div data-parallaxify-range="14" class="para mountain-1"></div>
            <div data-parallaxify-range="16" class="para mountain-2"></div>
            <div data-parallaxify-range="20" class="para ground"></div>
            <div data-parallaxify-range="36" class="para trex"></div>
            <div data-parallaxify-range="18" class="para page"></div>
            <div class="mask"></div>
        </div>
        <div class="error-msg">
            $errMsg        </div>
    </div>

<script src="https://su.yzcdn.cn/jquery-2.0.3.min.js"></script>
<script src="https://su.yzcdn.cn/jquery.parallaxify-0.0.2.min.js"></script>
<script>
$(function(){
    setTimeout(function() {
        $.parallaxify({
            positionProperty: 'transform',
            responsive: true,
            motionType: 'gaussian',
            mouseMotionType: 'gaussian',
            motionAngleX: 80,
            motionAngleY: 80,
            alphaFilter: 0.5,
            adjustBasePosition: true,
            alphaPosition: 0.125,
        });
    }, 200);
});
</script>
</body>
</html>
EOT;
