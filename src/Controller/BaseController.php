<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Story; 
use DateTime;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController {

    public function myDate() {
        return new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    }

    public static function getSlug($text) {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

//    public function getRandomProduct($limit = 4) {
//        $em = $this->getDoctrine()->getManager();
//        $sql = "SELECT `id` FROM `App::Story` order by RAND() limit $limit";
//        $result = $this->sql2Result($sql);
//        $rands = [];
//        foreach ($result as $v) {
//            $rands[] = $v['id'];
//        }
//
//        return $story = $em->getRepository(Product::class)->findBy(['id' => $rands]);
//    }
    public function redirectToReffer($request) {
        $url = $request->headers->get('referer');
        return $this->redirect($url);
    }
 function imageResizead($string, $type, $size) {
        $new_w = $size;
        $new_h = $size;
        //resize
        $src_img = imagecreatefromstring($string);

        $old_x = imageSX($src_img);
        $old_y = imageSY($src_img);

        if ($old_x > $old_y) {
            $thumb_w = $new_w;
            $thumb_h = $old_y * ($new_h / $old_x);
        }
        if ($old_x < $old_y) {
            $thumb_w = $old_x * ($new_w / $old_y);
            $thumb_h = $new_h;
        }
        if ($old_x == $old_y) {
            $thumb_w = $new_w;
            $thumb_h = $new_h;
        }

        $dst_img = imagecreatetruecolor($thumb_w, $thumb_h);
        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
        if ($type == 'image/png') {
            imagepng($dst_img);
        }
        if ($type == 'image/jpg' || $type == 'image/jpeg') {
            imagejpeg($dst_img);
        }
    }
    public function getRandomStoryPage($limit = 6) {
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT `id` FROM `story` order by RAND() limit $limit";
        $result = $this->sqlResult($sql);
        $rands = [];
        foreach ($result as $v) {
            $rands[] = $v['id'];
        }

        return $story = $em->getRepository(Story::class)->findBy(['id' => $rands]);
    }

    public function sql2Result($sql) {
        $conn = $this->getDoctrine()->getConnection();
        $stm = $conn->prepare($sql);
        $stm->execute();
        return $stm->fetchAll();
    }

    public function sqlResult($sql) {
        $conn = $this->getDoctrine()->getConnection();
        $stm = $conn->prepare($sql);
        $stm->execute();
        return $stm->fetchAll();
    }

    public function sendEtagResponse($response, $request, $catch = false) {

//        if ($this->getEnv() == 'dev') {
//            return $response;
//        }


        $encodings = $request->getEncodings();

        if (in_array('gzip', $encodings) && function_exists('gzencode')) {
            $content = gzencode($response->getContent());
            $response->setContent($content);
            $response->headers->set('Content-encoding', 'gzip');
        } elseif (in_array('deflate', $encodings) && function_exists('gzdeflate')) {
            $content = gzdeflate($response->getContent());
            $response->setContent($content);
            $response->headers->set('Content-encoding', 'deflate');
        }


        $response->setEtag(sha1($response->getContent()));
        $response->setPublic();
        if ($catch == true) {
            $response->setMaxAge(60 * 60 * 24);
        } else {
            $response->setMaxAge(60 * 5);
        }

        $response->isNotModified($request);
        return $response;
    }

    public function getEnv() {
        return $this->getParameter('kernel.environment');
    }

    public function getEntityManager() {
        return $this->getDoctrine()->getManager();
    }

    public function getUploadedFileInfo($file) {
        return $info = array(
            'name' => $file->getClientOriginalName(),
            'filename' => $file->getClientOriginalName(),
            'tmp_path' => $file->getRealPath(),
            'realPath' => $file->getRealPath(),
            'error' => $file->getError(),
            'mimetype' => $file->getClientMimeType(),
            'size' => $file->getClientSize(),
        );
    }

    public function getParents($cPos, $x) {
        $em = $this->getDoctrine()->getManager();
        $masterEntity = $em->getRepository(Category::class)->findOneBy(['isMaster' => true]);

        $masterId = $masterEntity->getId();

        if ($cPos == $masterId) {
            return 0;
        };

        $cat = $em->getRepository(Category::class)->findOneBy(['id' => $cPos]);
        $pid = $cat->getParentId();
        if ($pid != $masterId) {
            $newX = $x + 1;
            return $this->getParents($pid, $newX);
        } else {
            return $x;
        }
    }

    public function checkSubCat($id) {
        $em = $this->getDoctrine()->getManager();
        return $master = $em->getRepository(Category::class)->findBy(['parentId' => $id]);
    }

    public function getCategorySelect() {
        $em = $this->getDoctrine()->getManager();
        $master = $em->getRepository(Category::class)->findBy(['parentId' => 0]);
        $array = array();
        foreach ($master as $v) {
            $array[$v->getName()] = $v->getId();
            $sub = $this->checkSubCat($v->getId());
            if ($sub) {
                foreach ($sub as $subVal) {
                    $array["-- " . $subVal->getName()] = $subVal->getId();
                    $sub2 = $this->checkSubCat($subVal->getId());
                    if ($sub2) {
                        foreach ($sub2 as $sub2Val) {
                            $array["---- " . $sub2Val->getName()] = $sub2Val->getId();
                            $sub3 = $this->checkSubCat($sub2Val->getId());



                            if ($sub3) {
                                foreach ($sub3 as $sub3Val) {
                                    $array["------ " . $sub3Val->getName()] = $sub3Val->getId();
                                }
                            }
                        }
                    }
                }
            }
        }

        return $array;
    }

    public function imageResize($string, $type, $size, $text = "") {

//        $finder = new Finder();
//// find all files in the current directory
//        $finder->name('gomarice_tanomuze_cowboy.ttf');
//        $finder->files()->in("../src/Resources");
//        foreach ($finder as $file) {
//            $absoluteFilePath = $file->getRealPath();
//            //$fileNameWithExtension = $file->getRelativePathname();
//        }


        $new_w = $size;
        $new_h = $size;
        //resize
        $src_img = imagecreatefromstring($string);

        $old_x = imageSX($src_img);
        $old_y = imageSY($src_img);

        if ($old_x > $old_y) {
            $thumb_w = $new_w;
            $thumb_h = $old_y * ($new_h / $old_x);
        }
        if ($old_x < $old_y) {
            $thumb_w = $old_x * ($new_w / $old_y);
            $thumb_h = $new_h;
        }
        if ($old_x == $old_y) {
            $thumb_w = $new_w;
            $thumb_h = $new_h;
        }

        $dst_img = imagecreatetruecolor($thumb_w, $thumb_h);
//        $white = imagecolorallocate($src_img, 220, 220, 220);
//        $gray = imagecolorallocate($src_img, 50, 50, 50);
//        imagettftext($src_img, 24, 0, 7, $old_y - 3, $gray, $absoluteFilePath, $text);
//        imagettftext($src_img, 24, 0, 5, $old_y - 5, $white, $absoluteFilePath, $text);

        imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $thumb_w, $thumb_h, $old_x, $old_y);
        //$fontFilePath = '../Resources/ARLRDBD.TTF';

        ob_start();
        if ($type == 'image/png') {
            imagepng($dst_img);
        }
        if ($type == 'image/jpg' || $type == 'image/jpeg') {
            imagejpeg($dst_img);
        }
        $data = ob_get_contents();
        ob_end_clean();

        return $data;
    }

}
