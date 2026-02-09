<?php

require_once("sdk/v1/ucloud/proxy.php");


	class UFileStorageObjectApi
	{
		private $bucket;
		private $UCLOUD_PROXY_SUFFIX;
		private $UCLOUD_PUBLIC_KEY;
		private $UCLOUD_PRIVATE_KEY;


		public function __construct($option) {
			$this->bucket = $option['bucket'];
			$this->UCLOUD_PROXY_SUFFIX = $option['endpoint'];
			$this->UCLOUD_PUBLIC_KEY = $option['UCLOUD_PUBLIC_KEY'];
			$this->UCLOUD_PRIVATE_KEY = $option['UCLOUD_PRIVATE_KEY'];
		}


		public function Upload($key, $localFilePath) {
			//初始化分片上传,获取本地上传的uploadId和分片大小
			list($data, $err) = UCloud_MInit($this->UCLOUD_PROXY_SUFFIX, $this->UCLOUD_PUBLIC_KEY, $this->UCLOUD_PRIVATE_KEY, $this->bucket, $key);

			$uploadId = $data['UploadId'];
			$blkSize  = $data['BlkSize'];

			//数据上传
			list($etagList, $err) = UCloud_MUpload($this->UCLOUD_PROXY_SUFFIX, $this->UCLOUD_PUBLIC_KEY, $this->UCLOUD_PRIVATE_KEY, $this->bucket, $key, $localFilePath, $uploadId, $blkSize);

			//完成上传
			list($data, $err) = UCloud_MFinish($this->UCLOUD_PROXY_SUFFIX, $this->UCLOUD_PUBLIC_KEY, $this->UCLOUD_PRIVATE_KEY, $this->bucket, $key, $uploadId, $etagList);
			if ($err) {
				return False;
			} else {
				return True;
			}
		}


		public function Delete($keys) {
			foreach( $keys as $k => $v ){
				UCloud_Delete($this->UCLOUD_PROXY_SUFFIX, $this->UCLOUD_PUBLIC_KEY, $this->UCLOUD_PRIVATE_KEY, $this->bucket, $v);
			}
		}


		public function hasExist($key) {
			list($data, $err) = UCloud_Head($this->UCLOUD_PROXY_SUFFIX, $this->UCLOUD_PUBLIC_KEY, $this->UCLOUD_PRIVATE_KEY, $this->bucket, $key);
			if ($err) {
				return False;
			} else {
				return True;
			}
		}
	}
