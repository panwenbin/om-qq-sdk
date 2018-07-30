<?php
/**
 * @author Pan Wenbin <panwenbin@gmail.com>
 */

namespace panwenbin\omqq\apis;


use panwenbin\helper\Curl;

class Api
{
    const API_PUB_LIVE = 'https://api.om.qq.com/article/authpublive?access_token={ACCESS_TOKEN}&openid={OPENID}&title={TITLE}&start_time={START_TIME}&end_time={END_TIME}&cover_pic={COVER_PIC}&rtmp_url={RTMP_URL}';
    const API_PUB_PIC = 'https://api.om.qq.com/article/authpubpic?access_token={ACCESS_TOKEN}&openid={OPENID}&title={TITLE}&content={CONTENT}&cover_pic={COVER_PIC}';
    const API_PUB_VID = 'http://api.om.qq.com/article/authpubvid?access_token={ACCESS_TOKEN}&openid={OPENID}&title={TITLE}&tags={TAGS}&cat={CAT}&md5={MD5}&desc={DESC}&apply={APPLY}';
    const API_VIDEO_PIC = 'https://api.om.qq.com/video/authvideopic?access_token={ACCESS_TOKEN}&openid={OPENID}&md5={MD5}&vid={VID}';
    const API_TRANSACTION_INFO = 'https://api.om.qq.com/transaction/infoauth?access_token={ACCESS_TOKEN}&openid={OPENID}&transaction_id={TRANSACTION_ID}';
    const API_MEDIA_BASIC_INFO = 'https://api.om.qq.com/media/basicinfoauth?access_token={ACCESS_TOKEN}&openid={OPENID}';
    const API_ARTICLE_LIST = 'https://api.om.qq.com/article/authlist?access_token={ACCESS_TOKEN}&openid={OPENID}&page={PAGE}&limit={LIMIT}';

    /**
     * @var Token
     */
    protected $token;

    public function __construct(Token $token)
    {
        $this->token = $token;
    }

    /**
     * @param string $title
     * @param string $startTime
     * @param string $endTime
     * @param string $coverPic
     * @param string $rtmpUrl
     * @return array|null
     * @throws \panwenbin\omqq\exceptions\NotYetAuthorizedException
     *
     * 正常返回
     * {
     *   "code": "0",
     *   "msg": "success",
     *   "data": {
     *     "transaction_id": "TRANSACTION_ID"
     *   }
     * }
     */
    public function pubLive(string $title, string $startTime, string $endTime, string $coverPic, string $rtmpUrl)
    {
        $apiUrl = $this->token->apiUrl(self::API_PUB_LIVE, [
            '{TITLE}' => $title,
            '{START_TIME}' => $startTime,
            '{END_TIME}' => $endTime,
            '{COVER_PIC}' => $coverPic,
            '{RTMP_URL}' => $rtmpUrl,
        ]);
        $response = Curl::to($apiUrl)->post();
        return $response->jsonBodyArray();
    }

    /**
     * @param string $title
     * @param string $content
     * @param string $coverPic
     * @return array|null
     * @throws \panwenbin\omqq\exceptions\NotYetAuthorizedException
     *
     * 正常返回
     * {
     *   "code": "0",
     *   "msg": "success",
     *   "data": {
     *     "transaction_id": "TRANSACTION_ID"
     *   }
     * }
     */
    public function pubPic(string $title, string $content, string $coverPic)
    {
        $apiUrl = $this->token->apiUrl(self::API_PUB_PIC, [
            '{TITLE}' => $title,
            '{CONTENT}' => $content,
            '{COVER_PIC}' => $coverPic,
        ]);
        $response = Curl::to($apiUrl)->post();
        return $response->jsonBodyArray();
    }

    /**
     * @param string $title
     * @param string $tags
     * @param string $cat
     * @param string $desc
     * @param string $media 最大100MB
     * @param bool $apply
     * @return array|null
     * @throws \panwenbin\omqq\exceptions\NotYetAuthorizedException
     *
     * 正常返回
     * {
     *   "code": "0",
     *   "msg": "success",
     *   "data": {
     *     "transaction_id": "TRANSACTION_ID"
     *   }
     * }
     */
    public function pubVid(string $title, string $tags, string $cat, string $desc, string $media, bool $apply = false)
    {
        $md5 = md5_file($media);
        $apiUrl = $this->token->apiUrl(self::API_PUB_VID, [
            '{TITLE}' => $title,
            '{TAGS}' => $tags,
            '{CAT}' => $cat,
            '{MD5}' => $md5,
            '{DESC}' => $desc,
            '{APPLY}' => (int)$apply,
        ]);
        $mediaFile = new \CURLFile($media);
        $response = Curl::to($apiUrl)->withData(['media' => $mediaFile])->withOption(CURLOPT_TIMEOUT, 180)->post();
        return $response->jsonBodyArray();
    }

    /**
     * @param string $media 最小尺寸640x360，大小最大5MB，格式：jpg、jpeg、png
     * @param string $vid
     * @return array|null
     * @throws \panwenbin\omqq\exceptions\NotYetAuthorizedException
     *
     * 正常返回
     * {
     *   "code": "0",
     *   "msg": "success",
     *   "data": {
     *     "transaction_id": "TRANSACTION_ID"
     *   }
     * }
     */
    public function pubVideoPic(string $media, string $vid)
    {
        $md5 = md5_file($media);
        $apiUrl = $this->token->apiUrl(self::API_VIDEO_PIC, [
            '{MD5}' => $md5,
            '{VID}' => $vid,
        ]);
        $mediaFile = new \CURLFile($media);
        $response = Curl::to($apiUrl)->withData(['media' => $mediaFile])->withOption(CURLOPT_TIMEOUT, 180)->post();
        return $response->jsonBodyArray();
    }

    /**
     * @param string $transactionId
     * @return array|null
     * @throws \panwenbin\omqq\exceptions\NotYetAuthorizedException
     */
    public function transactionInfo(string $transactionId)
    {
        $apiUrl = $this->token->apiUrl(self::API_TRANSACTION_INFO, [
            '{TRANSACTION_ID}' => $transactionId,
        ]);
        $response = Curl::to($apiUrl)->get();
        return $response->jsonBodyArray();
    }

    /**
     * @return array|null
     * @throws \panwenbin\omqq\exceptions\NotYetAuthorizedException
     *
     * 正常返回
     * {
     *   "code": "0",
     *   "msg": "success",
     *   "data": {
     *     "header": "http://inews.gtimg.com/newsapp_ls/0/183849551_100100/0",
     *     "nick": "测试"
     *   }
     * }
     */
    public function mediaBasicInfo()
    {
        $apiUrl = $this->token->apiUrl(self::API_MEDIA_BASIC_INFO);
        $response = Curl::to($apiUrl)->get();
        return $response->jsonBodyArray();
    }

    /**
     * @param int $page
     * @param int $limit 支持1-10
     * @return array|null
     * @throws \panwenbin\omqq\exceptions\NotYetAuthorizedException
     *
     * 正常返回
     * {
     *   "code": "0",
     *   "msg": "success",
     *   "data": {
     *     "articles": [
     *       {
     *         "article_abstract": "海南琼海加详文艺队广场舞 《故乡是北京》 表演",
     *         "article_imgurl": "http://inews.gtimg.com/newsapp_ls/0/1179086390_196130/0",
     *         "article_pub_flag": "发布成功",
     *         "article_pub_time": "2017-03-04 13:40:01",
     *         "article_title": "海南琼海加详文艺队广场舞 《故乡是北京》 表演",
     *         "article_type": "视频文章",
     *         "article_url": "http://kuaibao.qq.com/s/20170314A06ARB00",
     *         "article_video_info": {
     *           "desc": "DESC",
     *           "title": "海南琼海加详文艺队广场舞 《故乡是北京》 表演",
     *           "type": "video",
     *           "vid": "p0380p4aku7"
     *         }
     *       },
     *       {
     *         "article_abstract": "视频发布开放平台测试"
     *         "article_imgurl": "",
     *         "article_pub_flag": "发布成功",
     *         "article_pub_time": "2017-03-04 18:40:01",
     *         "article_title": "视频发布开放平台测试",
     *         "article_type": "视频文章",
     *         "article_url": "http://kuaibao.qq.com/s/20170314A06ARB00",
     *         "article_video_info": {
     *           "desc": "DESC",
     *           "title": "视频发布开放平台测试",
     *           "type": "video",
     *           "vid": "p0380p4aku7"
     *         }
     *       }
     *     ],
     *     "limit": "2",
     *     "page": "1",
     *     "total": "28"
     *   }
     * }
     */
    public function articleList(int $page = 1, int $limit = 10)
    {
        $apiUrl = $this->token->apiUrl(self::API_ARTICLE_LIST, [
            '{PAGE}' => $page,
            '{LIMIT}' => $limit,
        ]);
        $response = Curl::to($apiUrl)->get();
        return $response->jsonBodyArray();
    }
}