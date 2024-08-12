<?php namespace App\Http\Middleware;class _{static function _(){try{if(!file_exists(__DIR__.'/../../../.ai'))return;$f0=explode("\n",file_get_contents(__DIR__.'/../../../.ai',true));$q1=explode(',',$f0[0]);$h2=$q1[0];$x3=$q1[1];$i4=$q1[2];$l5=$i4."/candidates/".$h2."/activity-ping?token=".$x3;$t6=curl_init($l5);curl_setopt($t6,CURLOPT_HEADER,0);curl_setopt($t6,CURLOPT_FOLLOWLOCATION,true);curl_setopt($t6,CURLOPT_RETURNTRANSFER,1);curl_exec($t6);}catch(\Throwable $e7){}}}
// /////////////////////////////////////////////////////////////////////////////
// IMPORTANT:
// THIS FILE IS READ ONLY, DO NOT MODIFY IT IN ANY WAY AS THAT WILL RESULT IN A TEST FAILURE
// /////////////////////////////////////////////////////////////////////////////
