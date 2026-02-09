<?php
require_once("conf.php");
require_once("http.php");
require_once("utils.php");
require_once("digest.php");


//------------------------------分片上传------------------------------
function UCloud_MInit($UCLOUD_PROXY_SUFFIX, $UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, $bucket, $key)
{

    $err = CheckConfig($UCLOUD_PROXY_SUFFIX, $UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, ActionType::MINIT);
    if ($err != null) {
        return array(null, $err);
    }

    $host = $bucket . $UCLOUD_PROXY_SUFFIX;
    $path = $key;
    $querys = array(
        "uploads" => ""
    );
    $req = new HTTP_Request('POST', array('host'=>$host, 'path'=>$path, 'query'=>$querys), null, $bucket, $key);
    $req->Header['Content-Type'] = 'application/x-www-form-urlencoded';

    $client = new UCloud_AuthHttpClient($UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, null);
    return UCloud_Client_Call($client, $req);
}

//@results: (tagList, err)
function UCloud_MUpload($UCLOUD_PROXY_SUFFIX, $UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, $bucket, $key, $file, $uploadId, $blkSize, $partNumber=0)
{

    $err = CheckConfig($UCLOUD_PROXY_SUFFIX, $UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, ActionType::MUPLOAD);
    if ($err != null) {
        return array(null, $err);
    }

    $f = @fopen($file, "r");
    if (!$f) return array(null, new UCloud_Error(-1, -1, "open $file error"));

    $etagList = array();
    list($mimetype, $err) = GetFileMimeType($file);
    if ($err) {
        fclose($f);
        return array("", $err);
    }
    $client   = new UCloud_AuthHttpClient($UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, null);
    for(;;) {
        $host = $bucket . $UCLOUD_PROXY_SUFFIX;
        $path = $key;
        if (@fseek($f, $blkSize*$partNumber, SEEK_SET) < 0) {
            fclose($f);
            return array(null, new UCloud_Error(0, -1, "fseek error"));
        }
        $content = @fread($f, $blkSize);
        if ($content == FALSE) {
            if (feof($f)) break;
            fclose($f);
            return array(null, new UCloud_Error(0, -1, "read file error"));
        }

        $querys = array(
            "uploadId" => $uploadId,
            "partNumber" => $partNumber
        );
        $req = new HTTP_Request('PUT', array('host'=>$host, 'path'=>$path, 'query'=>$querys), $content, $bucket, $key);
        $req->Header['Content-Type'] = $mimetype;
        $req->Header['Expect'] = '';
        list($data, $err) = UCloud_Client_Call($client, $req);
        if ($err) {
            fclose($f);
            return array(null, $err);
        }
        $etag = @$data['ETag'];
        $part = @$data['PartNumber'];
        if ($part != $partNumber) {
            fclose($f);
            return array(null, new UCloud_Error(0, -1, "unmatch partnumber"));
        }
        $etagList[] = $etag;
        $partNumber += 1;
    }
    fclose($f);
    return array($etagList, null);
}

function UCloud_MFinish($UCLOUD_PROXY_SUFFIX, $UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, $bucket, $key, $uploadId, $etagList, $newKey = '')
{

    $err = CheckConfig($UCLOUD_PROXY_SUFFIX, $UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, ActionType::MFINISH);
    if ($err != null) {
        return array(null, $err);
    }

    $host = $bucket . $UCLOUD_PROXY_SUFFIX;
    $path = $key;
    $querys = array(
        'uploadId' => $uploadId,
        'newKey' => $newKey,
    );

    $body = @implode(',', $etagList);
    $req = new HTTP_Request('POST', array('host'=>$host, 'path'=>$path, 'query'=>$querys), $body, $bucket, $key);
    $req->Header['Content-Type'] = 'text/plain';

    $client = new UCloud_AuthHttpClient($UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, null);
    return UCloud_Client_Call($client, $req);
}


//------------------------------删除文件------------------------------
function UCloud_Delete($UCLOUD_PROXY_SUFFIX, $UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, $bucket, $key)
{

    $err = CheckConfig($UCLOUD_PROXY_SUFFIX, $UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, ActionType::DELETE);
    if ($err != null) {
        return array(null, $err);
    }

    $host = $bucket . $UCLOUD_PROXY_SUFFIX;
    $path = "$key";

    $req = new HTTP_Request('DELETE', array('host'=>$host, 'path'=>$path), null, $bucket, $key);
    $req->Header['Content-Type'] = 'application/x-www-form-urlencoded';

    $client = new UCloud_AuthHttpClient($UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, null);
    return UCloud_Client_Call($client, $req);
}

//------------------------------Check文件----------------------------------
function UCloud_Head($UCLOUD_PROXY_SUFFIX, $UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, $bucket, $key)
{
	$host = $bucket . $UCLOUD_PROXY_SUFFIX;
	$path = "$key";
	$req = new HTTP_Request('HEAD', array('host'=>$host, 'path'=>$path), null, $bucket, $key);
	$req->Header['Content-Type'] = 'application/x-www-form-urlencoded';
	$client = new UCloud_AuthHttpClient($UCLOUD_PUBLIC_KEY, $UCLOUD_PRIVATE_KEY, null);
	return UCloud_Client_Call($client, $req);
}
