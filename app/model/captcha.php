<?php

namespace Scam;

class CaptchaModel extends Model {
    /* returns a captcha [code, img_data]; either from the captcha pool
    or by generating a new one. this is a DoS protection - a malicious user/bot can only
    create max 20 captchas, then he gets back the already existing. If one is solved, it gets deleted from the pool.
    */
    public function get() {
        $captcha = null;
        # create a new unless pool is full
        $poolSize = 20;
        $count = $this->count();
        if($count < $poolSize) {
            $code = $this->create();
            $captcha = [$code, $this->getByCode($code)];
        }
        else {
            # get one from pool if it's full
            $captcha = $this->getRandomCaptcha();
        }
        return $captcha;
    }

    public function check($code, $try) {
        # comparison safe against timing attacks, according to http://stackoverflow.com/questions/25844354/timing-attack-in-php
        $nonce = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);
        $valid = hash_hmac('sha256', $code, $nonce) === hash_hmac('sha256', $try, $nonce);
        if($valid) {
            # captcha solved, remove it
            $this->delete($code);
        }
        return $valid;
    }

    private function delete($code) {
        $q = $this->db->prepare('DELETE FROM captchas WHERE code = :code');
        return $q->execute([':code' => $code]);
    }

    private function getRandomCaptcha() {
        $q = $this->db->prepare('SELECT code, image FROM captchas ORDER BY RAND() LIMIT 0,1');
        $code = null;
        $image = null;
        $q->bindColumn(1, $code);
        $q->bindColumn(2, $image, \PDO::PARAM_LOB);
        $q->execute();
        $ret = $q->fetch(\PDO::FETCH_BOUND);
        return $ret ? [$code, $image] : null;
    }

    private function getByCode($code) {
        $q = $this->db->prepare('SELECT image FROM captchas WHERE code = :code LIMIT 1');

        $image = null;
        $q->bindColumn(1, $image, \PDO::PARAM_LOB);
        $q->execute([':code' => $code]);
        $ret = $q->fetch(\PDO::FETCH_BOUND);
        return $ret ? $image : null;
    }

    private function count() {
        $q = $this->db->prepare('SELECT COUNT(code) AS cnt FROM captchas');
        $q->execute();
        $c = $q->fetch();
        return $c ? $c->cnt : 0;
    }

    /* creates a captcha using imagemagick and saves it to the database */
    private function create($length = 5) {
        $code = $this->getFreeCode($length);
        $fontWidth = 48;
        $imgWidth = $length * $fontWidth;
        $imgHeight = 80;
        $tmpPath = tempnam(sys_get_temp_dir(), 'captcha');

        # get random colors
        list($bgColor, $color) = $this->newColors();

        $cmd = "convert -size ${imgWidth}x${imgHeight} " .
            "xc:'$bgColor' " .
            "-fill $color " .
            "-stroke $color " .
            "-strokewidth 1 " .
            "-font Verdana-Standard " .
            "-pointsize 40 ";

        # position each of the generated characters on image
        $offsetX = -($imgWidth/2) + $fontWidth / 2;
        for($i = 0; $i < $length; $i++) {
            $x = $offsetX + mt_rand(-5, 5);
            $y = mt_rand(-10, 10);

            # rotate & skew (max angle = 30°)
            $angle = 30;
            $r = mt_rand(-($angle), $angle);
            $s = mt_rand(-($angle), $angle);
            $cmd .= "-draw 'translate ${x},${y} rotate ${r} skewX ${s} gravity center text 0,0 '\\''${code[$i]}'\\''' ";

            $offsetX += $fontWidth;
        }

        # distort image (wave, implode, blur)
        $w = mt_rand(40, 60);
        $cmd .= "-background '${bgColor}' -wave 2x${w} -implode 0.2 -blur 1x3 -resize ${imgWidth}x${imgHeight}! ${tmpPath}.png 2>&1";

        $ret = -1;
        $output = [];
        exec($cmd, $output, $ret);
        if($ret != 0) {
          throw new \Exception('Error while creating captcha');
        }
        try {
            $sql = 'INSERT INTO captchas (code, image) VALUES (:code, :image)';
            $query = $this->db->prepare($sql);
            $query->bindValue(':code', $code);
            $image = fopen($tmpPath. ".png", 'rb');
            $query->bindParam(':image', $image, \PDO::PARAM_LOB);

            $result = $query->execute();
            if(!$result) {
                throw new \Exception('Error while saving captcha');
            }
            unlink($tmpPath . ".png");
            unlink($tmpPath);
            return $code;
        }
        catch(\Exception $e) {
            unlink($tmpPath . ".png");
            unlink($tmpPath);
            throw $e;
        }
    }

    private function getFreeCode($length) {
        $code = null;
        do {
            $try = substr($this->getRandomStr(), 0, $length);
            $q = $this->db->prepare('SELECT code FROM captchas WHERE code = :code');

            $q->execute([':code' => $try]);
            if(!$q->fetch()) {
                $code = $try;
            }
        } while($code == null);

        return $code;
    }

    private function newColors() {
        $bg = $this->newBackgroundColor();
        return ["rgb($bg[0], $bg[1], $bg[2])", $this->getTextColor($bg[0], $bg[1], $bg[2])];
    }

    /*
     * Generate a new, nice background color by mixing in our used orange, according to
     * http://stackoverflow.com/questions/43044/algorithm-to-randomly-generate-an-aesthetically-pleasing-color-palette#43044
     */
    private function newBackgroundColor() {
        # our orange as mix color:
        $mixRed = 255;
        $mixGreen = 203;
        $mixBlue = 107;
        $newRed = (mt_rand(0, 255) + $mixRed) /2;
        $newGreen = (mt_rand(0, 255) + $mixGreen) /2;
        $newBlue = (mt_rand(0, 255) + $mixBlue) /2;
        return [$newRed, $newGreen, $newBlue];
    }

    /* gets either black or white (whats better readable), according to this: http://24ways.org/2010/calculating-color-contrast/ */
    private function getTextColor($r, $g, $b) {
        $yiq = (($r*299)+($g*587)+($b*114))/1000;
        return ($yiq >= 128) ? 'black' : 'white';
    }

}