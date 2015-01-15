<?php
class ModelToolImage extends Model {
    /**
     *
     *	@param filename string
     *	@param width
     *	@param height
     *	@param type char [default, w, h]
     *				default = scale with white space,
     *				w = fill according to width,
     *				h = fill according to height
     *
     */

    public function checkImage($filename){
        $imageTypes = array('jpg','png','gif','jpeg','bmp','JPG','PNG','GIF','JPEG','BMP');
        if(file_exists(DIR_IMAGE . $filename) and is_file(DIR_IMAGE . $filename)){
            $base_name_arr = explode('.',$filename);
            $base_name = $base_name_arr[(count($base_name_arr)-1)];
            if(in_array($base_name,$imageTypes)){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    public function checkImageSize($filename){
        $size_limit = 12;
        if(file_exists(DIR_IMAGE . $filename) and is_file(DIR_IMAGE . $filename)){
            $filesize=filesize(DIR_IMAGE.$filename);
            if($filesize>=$size_limit){
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    public function resize($filename, $width, $height, $type = "", $waterMask = false) {

        if(!($width*$height)) {
            return false;
        }

        if (!file_exists(DIR_IMAGE . $filename) || !is_file(DIR_IMAGE . $filename)) {
            return;
        }

        $info = pathinfo($filename);

        $extension = $info['extension'];

        $old_image = $filename;
        $new_image = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . $type .'.' . $extension;

        if (!file_exists(DIR_IMAGE . $new_image) || (filemtime(DIR_IMAGE . $old_image) > filemtime(DIR_IMAGE . $new_image))) {

            $path = '';

            $directories = explode('/', dirname(str_replace('../', '', $new_image)));

            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;

                if (!file_exists(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }

            list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $old_image);

            if ($width_orig != $width || $height_orig != $height) {
                $image = new Image(DIR_IMAGE . $old_image);
                $image->resize($width, $height, $type);
                $image->save(DIR_IMAGE . $new_image);
                if($waterMask){
                    $thisImagePosition = DIR_IMAGE . $new_image;
                    $this->watermark($thisImagePosition, DIR_IMAGE . 'water_mask_png24.png',7);
                }
            } else {
                copy(DIR_IMAGE . $old_image, DIR_IMAGE . $new_image);
            }
        }
        //echo HTTP_SERVER . 'image/' . $new_image;
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            //return HTTP_SERVER . 'image/' . $new_image;
            return 'image/' . $new_image;
        } else {
            //return DIR_IMAGE . $new_image;
            return $new_image;
        }

    }

    public function resizeNoBlank($filename, $width, $height, $type = "", $waterMask = false){

        if(!($width*$height)) {
            return false;
        }

        $filename = urldecode($filename);
        if (!file_exists(DIR_IMAGE . $filename) || !is_file(DIR_IMAGE . $filename)) {
            //return;
            $filename = DEFAULT_RESTAURANT_IMAGE;
        }

        $checkImage = $this->checkImage($filename);
        if(!$checkImage){
            $filename = DEFAULT_RESTAURANT_IMAGE;
        }

        //check the image size
        $small_file = false;
        $checkImageSize = $this->checkImageSize($filename);
        if(!$checkImageSize){
            $small_file = true;
        }

        $info = pathinfo($filename);
        $extension = $info['extension'];
        $old_image = $filename;
        $new_image = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $width . 'x' . $height . $type .'.' . $extension;
        if (!file_exists(DIR_IMAGE . $new_image) || (filemtime(DIR_IMAGE . $old_image) > filemtime(DIR_IMAGE . $new_image))) {
            $path = '';
            $directories = explode('/', dirname(str_replace('../', '', $new_image)));
            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;

                if (!file_exists(DIR_IMAGE . $path)) {
                    @chmod(DIR_IMAGE . $path, 0777);
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }

            if(!$small_file){
                if(file_exists(DIR_IMAGE . $old_image) and is_file(DIR_IMAGE . $old_image)){
                    list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $old_image);
                }

                $newWidth = '';
                $newHeight = '';
                $top_x = '';
                $top_y = '';
                $targetScale = $width / $height;
                $sourceScale = $width_orig / $height_orig;
                if($sourceScale > $targetScale){
                    $newWidth = $height_orig * $targetScale;
                    $newHeight = $height_orig;
                }else{
                    $newWidth = $width_orig;
                    $newHeight = $width_orig / $targetScale;
                }
                $top_x = ($width_orig - $newWidth ) / 2 ;
                $top_y = ($height_orig - $newHeight ) / 2 ;
                if ($width_orig != $width || $height_orig != $height) {
                    $image = new Image(DIR_IMAGE . $old_image);
                    $image->crop($top_x, $top_y, $top_x + $newWidth, $top_y + $newHeight);
                    $image->resize($width, $height, $type);
                    $image->save(DIR_IMAGE . $new_image);
                    if($waterMask){
                        $thisImagePosition = DIR_IMAGE . $new_image;
                        $this->watermark($thisImagePosition, DIR_IMAGE . 'water_mask_png24.png',7);
                    }
                } else {
                    copy(DIR_IMAGE . $old_image, DIR_IMAGE . $new_image);
                }
            }
            else{
                copy(DIR_IMAGE . $old_image, DIR_IMAGE . $new_image);
            }

        }

        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            return HTTP_SERVER . 'image/' . $new_image;
        } else {
            return HTTP_SERVER . 'image/' . $new_image;
            //return 'image/' . $new_image;
        }
    }

    public function resizeByMask($filename, $width, $height, $type = "", $waterMask = false){

        if(!($width*$height)) {
            return false;
        }

        if (!file_exists(DIR_IMAGE . $filename) || !is_file(DIR_IMAGE . $filename)) {
            return;
        }
        $ret = array();
        $info = pathinfo($filename);
        $extension = $info['extension'];
        $old_image = $filename;

        list($width_orig, $height_orig) = getimagesize(DIR_IMAGE . $old_image);
        $newWidth = '';
        $newHeight = '';
        $top_x = '';
        $top_y = '';
        $targetScale = $width / $height;
        $sourceScale = $width_orig / $height_orig;
        if($sourceScale > $targetScale){
            $newWidth = $height * $sourceScale;
            $newHeight = $height;
        }else{
            $newWidth = $width;
            $newHeight = $width / $sourceScale;
        }
        $newWidth = intval($newWidth);
        $newHeight = intval($newHeight);
        $top_x = ($width - $newWidth ) / 2 ;
        $top_y = ($height - $newHeight ) / 2 ;
        $ret['offsetX'] = $top_x;
        $ret['offsetY'] = $top_y;
        $ret['width'] = $newWidth;
        $ret['height'] = $newHeight;
        $new_image = 'cache/' . utf8_substr($filename, 0, utf8_strrpos($filename, '.')) . '-' . $newWidth . 'x' . $newHeight . $type .'.' . $extension;

        if (!file_exists(DIR_IMAGE . $new_image) || (filemtime(DIR_IMAGE . $old_image) > filemtime(DIR_IMAGE . $new_image))) {
            $path = '';
            $directories = explode('/', dirname(str_replace('../', '', $new_image)));
            foreach ($directories as $directory) {
                $path = $path . '/' . $directory;
                if (!file_exists(DIR_IMAGE . $path)) {
                    @mkdir(DIR_IMAGE . $path, 0777);
                }
            }
            if ($width_orig != $width || $height_orig != $height) {
                $image = new Image(DIR_IMAGE . $old_image);
                $image->resize($newWidth, $newHeight, $type);
                $image->save(DIR_IMAGE . $new_image);
                if($waterMask){
                    $thisImagePosition = DIR_IMAGE . $new_image;
                    $this->watermark($thisImagePosition, DIR_IMAGE . 'water_mask_png24.png',7);
                }
            } else {
                copy(DIR_IMAGE . $old_image, DIR_IMAGE . $new_image);
            }
        }
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $ret['imgUrl'] = HTTP_SERVER . 'image/' . $new_image;
        } else {
            $ret['imgUrl'] = HTTP_SERVER . 'image/' . $new_image;
        }
        return $ret;
    }

    function watermark($img, $watermark, $district = 0,$watermarkquality = 95){
        $imginfo = @getimagesize($img);
        $watermarkinfo = @getimagesize($watermark);
        $img_w = $imginfo[0];
        $img_h = $imginfo[1];
        $watermark_w = $watermarkinfo[0];
        $watermark_h = $watermarkinfo[1];
        if($district == 0) $district = rand(1,9);
        if(!is_int($district) OR 1 > $district OR $district > 9) $district = 9;
        switch($district){
            case 1:
                $x = +5;
                $y = +5;
                break;
            case 2:
                $x = ($img_w - $watermark_w) / 2;
                $y = +5;
                break;
            case 3:
                $x = $img_w - $watermark_w - 5;
                $y = +5;
                break;
            case 4:
                $x = +5;
                $y = ($img_h - $watermark_h) / 2;
                break;
            case 5:
                $x = ($img_w - $watermark_w) / 2;
                $y = ($img_h - $watermark_h) / 2;
                break;
            case 6:
                $x = $img_w - $watermark_w;
                $y = ($img_h - $watermark_h) / 2;
                break;
            case 7:
                $x = + 10;
                $y = $img_h - $watermark_h - 10;
                break;
            case 8:
                $x = ($img_w - $watermark_w) / 2;
                $y = $img_h - $watermark_h - 5;
                break;
            case 9:
                $x = $img_w - $watermark_w - 5;
                $y = $img_h - $watermark_h - 5;
                break;
        }
        switch ($imginfo[2]) {
            case 1:
                $im = @imagecreatefromgif($img);
                break;
            case 2:
                $im = @imagecreatefromjpeg($img);
                break;
            case 3:
                $im = @imagecreatefrompng($img);
                break;
        }
        $watermark_logo = '';
        switch ($watermarkinfo[2]) {
            case 1:
                $watermark_logo = @imagecreatefromgif($watermark);
                break;
            case 2:
                $watermark_logo = @imagecreatefromjpeg($watermark);
                break;
            case 3:
                $watermark_logo = @imagecreatefrompng($watermark);
                break;
        }
        if(!$im or !$watermark_logo) return false;
        $dim = @imagecreatetruecolor($img_w, $img_h);
        if(@imagecopy($dim, $im, 0, 0, 0, 0,$img_w,$img_h )){
            imagecopy($dim, $watermark_logo, $x, $y, 0, 0, $watermark_w, $watermark_h);
        }
        $file = dirname($img) . '/' . basename($img);
        $result = imagejpeg ($dim,$file,$watermarkquality);
        imagedestroy($watermark_logo);
        imagedestroy($dim);
        imagedestroy($im);
        if($result){
            return $file;
        }
        else {
            return false;
        }
    }

    public function link($img){
        if (!file_exists(DIR_IMAGE . $img) || !is_file(DIR_IMAGE . $img)) {
            return;
        }
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            return $this->config->get('config_ssl') . 'image/' . $img;
        } else {
            return $this->config->get('config_url') . 'image/' . $img;
        }
    }

    /*public function preprocess($imageUrl,$defaultImageUrl) {

        $imageUrl = ltrim($imageUrl,'/');

        if(file_exists(DIR_IMAGE .urldecode($imageUrl)) && is_file(DIR_IMAGE . urldecode($imageUrl))) {
            $processedImageUrl =
        }else{

        }

    }*/

}
?>
