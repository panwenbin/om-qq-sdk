<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\omqq\models;


/**
 * Class Article
 * @package panwenbin\omqq\models
 */
class Article
{
    public $article_title;
    public $article_type;
    public $article_abstract;
    public $article_imgurl;
    public $article_pub_flag;
    public $article_pub_time;
    public $article_id;
    public $article_url;
    public $article_video_info = [
        'vid' => '',
        'title' => '',
        'desc' => '',
        'type' => '',
    ];
    public $article_pid;

    /**
     * @param array $array
     */
    public function fillWithArray(array $array)
    {
        foreach ($this as $key => $value) {
            if (isset($array[$key])) {
                $this->$key = $array[$key];
            }
        }
    }

    /**
     * @param array $array
     * @return Article
     */
    public static function newFromArray(array $array)
    {
        $article = new static();
        $article->fillWithArray($array);
        return $article;
    }
}