<?php

/**
 * phpspider - A PHP Framework For Crawler
 *
 * @package  phpspider
 * @author   Seatle Yang <seatle@foxmail.com>
 */

class phpspider
{
    /**
     * 爬虫爬取每个网页的时间间隔,0表示不延时，单位：秒
     */
    const INTERVAL = 0;

    /**
     * 爬虫爬取每个网页的超时时间，单位：秒 
     */
    const TIMEOUT = 5;

    /**
     * 爬取失败次数，不想失败重新爬取则设置为0 
     */
    const COLLECT_FAILS = 5;

    /**
     * 抽取规则的类型：xpath、jsonpath、regex 
     */
    const FIELDS_SELECTOR_TYPE = 'xpath';

    /**
     * 爬虫爬取网页所使用的浏览器类型：android，ios，pc，mobile
     */
    const AGENT_ANDROID = "Mozilla/5.0 (Linux; U; Android 6.0.1;zh_cn; Le X820 Build/FEXCNFN5801507014S) AppleWebKit/537.36 (KHTML, like Gecko)Version/4.0 Chrome/49.0.0.0 Mobile Safari/537.36 EUI Browser/5.8.015S";
    const AGENT_IOS = "Mozilla/5.0 (iPhone; CPU iPhone OS 9_3_3 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G34 Safari/601.1";
    const AGENT_PC = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36";
    const AGENT_MOBILE = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36";

    /**
     * HTTP请求的Header 
     */
    public static $headers = array();

    /**
     * HTTP请求的Cookie 
     */
    public static $cookies = array();

    /**
     * HTTP请求的Cookie，匹配domain 
     */
    public static $domain_cookies = array();

    /**
     * 试运行
     * 试运行状态下，程序持续三分钟或抓取到30条数据后停止
     */
    public static $test_run = true;

    /**
     * 配置 
     */
    public static $configs = array();

    /**
     * 要抓取的URL队列 
     md5(url) => array(
         'url'          => '',      // 要爬取的URL
         'url_type'     => '',      // 要爬取的URL类型,scan_page、list_page、content_page
         'method'       => 'get',   // 默认为"GET"请求, 也支持"POST"请求
         'headers'      => array(), // 此url的Headers, 可以为空
         'data'         => array(), // 发送请求时需添加的参数, 可以为空
         'context_data' => '',      // 此url附加的数据, 可以为空
         'repeat'       => false,   // 是否去重，true表示之前处理过的url也会插入待爬队列
         'proxy'        => false,   // 是否使用代理
         'proxy_auth'   => '',      // 代理验证: {$USER}:{$PASS}
         'collect_count'=> 0        // 抓取次数
         'collect_fails'=> 0        // 允许抓取失败次数
     ) 
     */
    public static $collect_queue_links = array();

    /**
     * 要抓取的URL数组
     * md5($url) => time()
     */
    public static $collect_urls = array();

    /**
     * 已经抓取过的URL数组
     * md5($url) => time()
     */
    public static $collected_urls = array();

    /**
     * 爬虫初始化时调用, 用来指定一些爬取前的操作 
     * 
     * @var mixed
     * @access public
     */
    public $on_start = null;

    /**
     * 切换IP代理后，先前请求网页用到的Cookie会被清除，这里可以再次添加 
     * 
     * @var mixed
     * @access public
     */
    public $on_change_proxy = null;

    /**
     * 判断当前网页是否被反爬虫，需要开发者实现 
     * 
     * @var mixed
     * @access public
     */
    public $is_anti_spider = null;

    /**
     * 在一个网页下载完成之后调用，主要用来对下载的网页进行处理 
     * 
     * @var mixed
     * @access public
     */
    public $on_download_page = null;

    /**
     * URL属于入口页 
     * 在爬取到入口url的内容之后, 添加新的url到待爬队列之前调用 
     * 主要用来发现新的待爬url, 并且能给新发现的url附加数据
     * 
     * @var mixed
     * @access public
     */
    public $on_scan_page = null;
    
    /**
     * URL属于列表页
     * 在爬取到列表页url的内容之后, 添加新的url到待爬队列之前调用 
     * 主要用来发现新的待爬url, 并且能给新发现的url附加数据
     * 
     * @var mixed
     * @access public
     */
    public $on_list_page = null;

    /**
     * URL属于内容页 
     * 在爬取到内容页url的内容之后, 添加新的url到待爬队列之前调用 
     * 主要用来发现新的待爬url, 并且能给新发现的url附加数据
     * 
     * @var mixed
     * @access public
     */
    public $on_content_page = null;

    /**
     * 当一个field的内容被抽取到后进行的回调, 在此回调中可以对网页中抽取的内容作进一步处理 
     * 
     * @var mixed
     * @access public
     */
    public $on_extract_field = null;

    /**
     * 在一个网页的所有field抽取完成之后, 可能需要对field进一步处理, 以发布到自己的网站 
     * 
     * @var mixed
     * @access public
     */
    public $on_extract_page = null;

    /**
     * 总共爬取链接数 
     */
    public static $collect_url_num = 0;

    /**
     * 成功爬取链接数
     */
    public static $collected_urls_num = 0;

    /**
     * 提取到的字段数 
     */
    public static $fields_num = 0;

    public static $export_type = '';
    public static $export_file = '';
    public static $export_conf = '';
    public static $export_table = '';

    function __construct($configs = array())
    {
        //$files = debug_backtrace();
        //$prev_file = $files[0]['file'];
        $included_files = get_included_files();
        $content = file_get_contents($included_files[0]);
        if (!preg_match("#/\* Do NOT delete this comment \*/#", $content) || !preg_match("#/\* 不要删除这段注释 \*/#", $content))
        {
            exit(util::colorize("未知错误；请参考文档或寻求技术支持。\n", 'fail'));
        }

        self::$configs = $configs;
        self::$configs['proxy']         = isset(self::$configs['proxy'])         ? self::$configs['proxy']         : '';
        self::$configs['proxy_auth']    = isset(self::$configs['proxy_auth'])    ? self::$configs['proxy_auth']    : '';
        self::$configs['user_agent']    = isset(self::$configs['user_agent'])    ? self::$configs['user_agent']    : self::AGENT_PC;
        self::$configs['interval']      = isset(self::$configs['interval'])      ? self::$configs['interval']      : self::INTERVAL;
        self::$configs['timeout']       = isset(self::$configs['timeout'])       ? self::$configs['timeout']       : self::TIMEOUT;
        self::$configs['collect_fails'] = isset(self::$configs['collect_fails']) ? self::$configs['collect_fails'] : self::COLLECT_FAILS;
        self::$configs['queue_length']  = isset(self::$configs['queue_length'])  ? self::$configs['queue_length']  : 0;
        self::$configs['export']        = isset(self::$configs['export'])        ? self::$configs['export']        : array();
    }

    public function add_useragent($useragent)
    {
        cls_curl::set_useragent($useragent);
    }

    /**
     * 一般在 on_start 回调函数中调用，用来添加一些HTTP请求的Header
     * 
     * @param mixed $url
     * @param mixed $options
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function add_header($key, $value)
    {
        self::$headers[$key] = $value;
    }

    /**
     * 一般在 on_start 回调函数中调用，用来得到某个域名所附带的某个Cookie
     * 
     * @param mixed $name
     * @param mixed $domain
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function get_cookie($name, $domain = '')
    {
        $cookies = empty($domain) ? self::$cookies : self::$domain_cookies[$domain];
        return isset($cookies[$name]) ? $cookies[$name] : '';
    }
    
    public function get_cookies($domain = '')
    {
        return empty($domain) ? self::$cookies : self::$domain_cookies[$domain];
    }

    /**
     * 一般在on_start回调函数中调用，用来添加一些HTTP请求的Cookie
     * 
     * @param mixed $cookies
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function add_cookie($key, $value, $domain = '/')
    {
        self::$cookies[$key] = $value;
    }

    /**
     * 一般在on_start回调函数中调用，用来添加一些HTTP请求的Cookie
     * 
     * @param mixed $cookies
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function add_cookies($cookies)
    {
        $cookies_arr = explode(";", $cookies);
        foreach ($cookies_arr as $cookie) 
        {
            $cookie_arr = explode("=", $cookie);
            self::$cookies[trim($cookie_arr[0])] = trim($cookie_arr[1]);
        }
    }

    /**
     * 一般在 on_scan_page 和 on_list_page 回调函数中调用，用来往待爬队列中添加url
     * 
     * @param mixed $url
     * @param mixed $options
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function add_url($url, $options = array())
    {
        $link = array(
            'url'           => $url,            
            'url_type'      => '', 
            'method'        => isset($options['method'])        ? $options['method']         : 'get',             
            'proxy'         => isset($options['proxy'])         ? $options['proxy']          : self::$configs['proxy'],             
            'proxy_auth'    => isset($options['proxy_auth'])    ? $options['proxy_auth']     : self::$configs['proxy_auth'],             
            'headers'       => isset($options['headers'])       ? $options['headers']        : self::$headers,    
            'data'          => isset($options['data'])          ? $options['data']           : array(),           
            'context_data'  => isset($options['context_data'])  ? $options['context_data']   : '',                
            'repeat'        => isset($options['repeat'])        ? $options['repeat']         : false,             
            'collect_count' => isset($options['collect_count']) ? $options['collect_count']  : 0,                 
            'collect_fails' => isset($options['collect_fails']) ? $options['collect_fails']  : self::$configs['collect_fails'],
        );
        // 放入爬虫队列
        array_push(self::$collect_queue_links, $link);
        // 放入抓取数组
        self::$collect_urls[md5($url)] = time();
    }

    public function start()
    {
        // 如果设置了导出选项
        if (!empty(self::$configs['export'])) 
        {
            self::$export_type = isset(self::$configs['export']['type']) ? self::$configs['export']['type'] : '';
            if (self::$export_type == 'csv') 
            {
                self::$export_file = isset(self::$configs['export']['file']) ? self::$configs['export']['file'] : '';
                if (empty(self::$export_file)) 
                {
                    exit(util::colorize(date("H:i:s") . " 设置了导出类型为CSV的导出文件不能为空\n\n", 'fail'));
                }
            }
            elseif (self::$export_type == 'sql') 
            {
                self::$export_file = isset(self::$configs['export']['file']) ? self::$configs['export']['file'] : '';
                self::$export_table = isset(self::$configs['export']['table']) ? self::$configs['export']['table'] : '';
                if (empty(self::$export_file)) 
                {
                    exit(util::colorize(date("H:i:s") . " 设置了导出类型为sql的导出文件不能为空\n\n", 'fail'));
                }
            }
            elseif (self::$export_type == 'db') 
            {
                self::$export_conf = isset(self::$configs['export']['conf']) ? self::$configs['export']['conf'] : '';
                self::$export_table = isset(self::$configs['export']['table']) ? self::$configs['export']['table'] : '';
                if (!empty(self::$export_conf)) 
                {
                    db::_init_mysql(self::$export_conf);
                    if (!db::table_exists(self::$export_table))
                    {
                        exit(util::colorize(date("H:i:s") . " 数据库表(".self::$export_table.")不存在\n\n", 'warn'));
                    }
                }
            }
        }

        if (empty(self::$configs['scan_urls'])) 
        {
            exit(date("H:i:s")." No scan url to start\n");
        }

        if ($this->on_start) 
        {
            call_user_func($this->on_start, $this);
        }

        foreach ( self::$configs['scan_urls'] as $url ) 
        {
            $parse_url_arr = parse_url($url);
            if (empty($parse_url_arr['host']) || !in_array($parse_url_arr['host'], self::$configs['domains'])) 
            {
                exit("scan_urls中的域名(\"{$parse_url_arr['host']}\")不匹配domains中的域名\n");
            }

            $link = array(
                'url'           => $url,                            // 要抓取的URL
                'url_type'      => 'scan_page',                     // 要抓取的URL类型
                'method'        => 'get',                           // 默认为"GET"请求, 也支持"POST"请求
                'headers'       => self::$headers,                  // 此url的Headers, 可以为空
                'data'          => array(),                         // 发送请求时需添加的参数, 可以为空
                'context_data'  => '',                              // 此url附加的数据, 可以为空
                'repeat'        => false,                           // 是否去重，true表示之前处理过的url也会插入待爬队列
                'proxy'         => self::$configs['proxy'],         // 代理服务器
                'proxy_auth'    => self::$configs['proxy_auth'],    // 代理验证
                'collect_count' => 0,                               // 抓取次数
                'collect_fails' => self::$configs['collect_fails'], // 允许抓取失败次数
            );
            // 放入爬虫队列
            array_push(self::$collect_queue_links, $link);
            // 放入抓取数组
            self::$collect_urls[md5($url)] = time();
            self::$collect_url_num++;
        }

        echo "\n爬虫开始测试, 将持续三分钟或抓取到30条数据后停止.\n\n";

        // 测试抓取页面
        //$this->get_contents("http://www.qiushibaike.com/article/117554075");
        //exit;

        // 抓取页面
        while(!empty(self::$collect_queue_links))
        { 
            // 从队列取出要爬取的URL对象
            $link = array_shift(self::$collect_queue_links); 
            // 从采集数组中排除这个URL
            unset(self::$collect_urls[md5($link['url'])]);
            $this->collect_page($link);
        } 

        echo date("H:i:s")." 爬取完成\n";
        echo "总共爬取链接数：".self::$collect_url_num."\n";
        echo "成功爬取链接数：".self::$collected_urls_num."\n";
    }

    /**
     * 爬取页面
     * 
     * @param mixed $collect_url    要抓取的链接
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function collect_page($link) 
    {
        $url = $link['url'];
        echo date("H:i:s")." 抓取队列长度：".count(self::$collect_urls)."\n\n";

        $html = $this->request_url($url, $link);
        if (!$html) 
        {
            return false;
        }

        if ($this->is_anti_spider) 
        {
            $is_anti_spider = call_user_func($this->is_anti_spider, $url, $html);
            // 如果在回调函数里面判断被反爬虫并且返回true
            if ($is_anti_spider) 
            {
                return false;
            }
        }

        // 当前正在爬取的网页页面的对象
        $page = array(
            'url'     => $url,
            'raw'     => $html,
            'request' => array(
                'url'           => $url,
                'method'        => $link['method'],
                'data'          => $link['data'],
                'context_data'  => $link['context_data'],
                'headers'       => $link['headers'],
                'collect_count' => $link['collect_count'],
                'collect_fails' => $link['collect_fails'],
            ),
        );

        // 在一个网页下载完成之后调用. 主要用来对下载的网页进行处理.
        if ($this->on_download_page) 
        {
            // 回调函数记得无论如何最后一定要 return $page;
            $page = call_user_func($this->on_download_page, $page, $this);
        }

        // 是否从当前页面分析提取URL
        $is_collect_url = true;
        if ($link['url_type'] == 'scan_page') 
        {
            if ($this->on_scan_page) 
            {
                // 回调函数如果返回false表示不需要再从此网页中发现待爬url
                $is_collect_url = call_user_func($this->on_scan_page, $page, $html, $this);
            }
        }
        elseif ($link['url_type'] == 'list_page') 
        {
            if ($this->on_list_page) 
            {
                $is_collect_url = call_user_func($this->on_list_page, $page, $html, $this);
            }
        }
        elseif ($link['url_type'] == 'content_page') 
        {
            if ($this->on_content_page) 
            {
                $is_collect_url = call_user_func($this->on_content_page, $page, $html, $this);
            }
        }

        // 成功才存入已爬取列表队列，避免过多操作数组
        self::$collected_urls[md5($url)] = time();

        echo date("H:i:s")." 网页下载成功：".$url."\n\n";

        if ($is_collect_url) 
        {
            // 分析提取HTML页面中的URL
            $this->get_html_urls($html, $url);
        }

        // 分析提取HTML页面中的字段
        $this->get_html_fields($html, $url, $link, $page);

        self::$collected_urls_num++;

        // 爬虫爬取每个网页的时间间隔，单位：秒
        if (!empty(self::$configs['interval'])) 
        {
            sleep(self::$configs['interval']);
        }
    }

    /**
     * 下载网页，得到网页内容
     * 
     * @param mixed $url
     * @param mixed $options
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function request_url($url, $options = array())
    {
        //$url = "http://www.qiushibaike.com/article/117568316";

        $pattern = "/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/";
        if(!preg_match($pattern, $url))
        {
            exit(util::colorize(date("H:i:s")." 你所请求的URL({$url})不是有效的HTTP地址\n\n", 'fail'));
        }

        $parse_url_arr = parse_url($url);
        $domain = $parse_url_arr['host'];

        $link = array(
            'url'           => $url,
            'method'        => isset($options['method'])        ? $options['method']         : 'get',             
            'proxy'         => isset($options['proxy'])         ? $options['proxy']          : self::$configs['proxy'],             
            'proxy_auth'    => isset($options['proxy_auth'])    ? $options['proxy_auth']     : self::$configs['proxy_auth'],             
            'headers'       => isset($options['headers'])       ? $options['headers']        : self::$headers,    
            'data'          => isset($options['data'])          ? $options['data']           : array(),           
            'context_data'  => isset($options['context_data'])  ? $options['context_data']   : '',                
            'repeat'        => isset($options['repeat'])        ? $options['repeat']         : false,             
            'collect_count' => isset($options['collect_count']) ? $options['collect_count']  : 0,                 
            'collect_fails' => isset($options['collect_fails']) ? $options['collect_fails']  : self::$configs['collect_fails'],
        );

        //ini_set('user_agent','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36');
        //$html = file_get_contents($url);

        cls_curl::set_timeout(self::$configs['timeout']);
        cls_curl::set_useragent(self::$configs['user_agent']);
        
        // 全局Cookie + 域名下的Cookie
        $cookies = self::$cookies;
        if (isset(self::$domain_cookies[$domain]) && is_array(self::$domain_cookies[$domain])) 
        {
            // 键名为字符时，＋把最先出现的值作为最终结果返回，array_merge()则会覆盖掉前面相同键名的值
            $cookies =  array_merge($cookies, self::$domain_cookies[$domain]);
        }

        // 是否设置了cookie
        if (!empty($cookies)) 
        {
            foreach ($cookies as $key=>$value) 
            {
                $cookie_arr[] = $key."=".$value;
            }
            $cookies = implode("; ", $cookie_arr);
            cls_curl::set_cookie($cookies);
        }

        // 是否设置了代理
        if (!empty($link['proxy'])) 
        {
            cls_curl::set_proxy($link['proxy'], $link['proxy_auth']);
        }

        // 如何设置了 HTTP Headers
        if (!empty($link['headers'])) 
        {
            cls_curl::set_headers($link['headers']);
        }

        // 如果设置了附加的数据，如json和xml，就直接发附加的数据,php端可以用 file_get_contents("php://input"); 获取
        $fields = empty($link['context_data']) ? $link['data'] : $link['context_data'];
        $method = strtolower($link['method']);
        cls_curl::set_http_raw(true);
        $html = cls_curl::$method($url, $fields);

        // 对于登录成功后302跳转的，Cookie实际上存在body而不在header，header只有一句：HTTP/1.1 100 Continue
        //if (cls_curl::get_http_code() == 302)
        //{
        //}

        // 为了兼容301和301这些乱七八糟的，还是header+body一起匹配吧
        // 解析Cookie并存入 self::$cookies 方便调用
        preg_match_all("/.*?Set\-Cookie: ([^\r\n]*)/i", $html, $matches);
        $cookies = empty($matches[1]) ? array() : $matches[1];

        // 解析到Cookie
        if (!empty($cookies)) 
        {
            $cookies = implode(";", $cookies);
            $cookies = explode(";", $cookies);
            foreach ($cookies as $cookie) 
            {
                $cookie_arr = explode("=", $cookie);
                // 过滤掉domain路径
                if (trim($cookie_arr[0]) == 'path') 
                {
                    continue;
                }
                // 从URL得到的Cookie不要放入全局，放到对应的域名下即可
                //self::$cookies[trim($cookie_arr[0])] = trim($cookie_arr[1]);
                self::$domain_cookies[$domain][trim($cookie_arr[0])] = trim($cookie_arr[1]);
            }
        }

        $http_code = cls_curl::get_http_code();
        if ($http_code != 200)
        {
            if ($http_code == 407) 
            {
                echo util::colorize(date("H:i:s")." 代理服务器验证失败，请检查代理服务器设置\n\n", 'fail');
                return false;
            }
            // 抓取次数 小于 允许抓取失败次数
            if ( $link['collect_count'] < $link['collect_fails'] ) 
            {
                $link['collect_count']++;
                // 扔回去继续采集
                array_push(self::$collect_queue_links, $link);
                self::$collect_urls[md5($url)] = time();
            }
            echo util::colorize(date("H:i:s")." 网页下载失败：".$url." 失败次数：".$link['collect_count']."\n\n", 'fail');
            return false;
        }

        $body = array();
        // 解析HTTP数据流
        if (!empty($html)) 
        {
            list($header, $body) = explode("\r\n\r\n", $html);
        }
        return $body;
    }

    /**
     * 分析提取HTML页面中的URL
     * 
     * @param mixed $html           HTML内容
     * @param mixed $collect_url    抓取的URL，用来拼凑完整页面的URL
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function get_html_urls($html, $collect_url) 
    { 
        $parse_url_arr = parse_url($collect_url);

        //--------------------------------------------------------------------------------
        // 正则匹配出页面中的URL
        //--------------------------------------------------------------------------------
        preg_match_all('/<a .*?href="(.*?)".*?>/is', $html, $out); 
        if (empty($out[1])) 
        {
            return false;
        }

        //--------------------------------------------------------------------------------
        // 过滤和拼凑URL
        //--------------------------------------------------------------------------------
        // 去除重复的RUL
        $urls = array_unique($out[1]);
        foreach ($urls as $k=>$v) 
        {
            // 排除JavaScript的连接
            if (strpos($v, "javascript:") !== false) 
            {
                unset($urls[$k]);
                continue;
            }

            $arr = parse_url($v);

            if (empty($arr['path'])) 
            {
                unset($urls[$k]);
                continue;
            }

            // 如果host不为空，判断是不是要爬取的域名
            if (!empty($arr['host'])) 
            {
                // 排除非域名下的url以提高爬取速度
                if (!in_array($arr['host'], self::$configs['domains'])) 
                {
                    unset($urls[$k]);
                    continue;
                }
            }
            else
            {
                $urls[$k] = $parse_url_arr['scheme'].'://'.str_replace("//", "/", $parse_url_arr['host']."/".$v);
            }
        }

        if (empty($urls)) 
        {
            //echo date("H:i:s")." 网页没有连接：".$collect_url."\n";
            return false;
        }

        //--------------------------------------------------------------------------------
        // 把抓取到的URL放入队列
        //--------------------------------------------------------------------------------
        foreach ($urls as $url) 
        {
            foreach (self::$configs['list_url_regexes'] as $regex) 
            {
                // 如果是列表链接
                // 不存在已爬取数组
                // 不存在爬取数组
                if (preg_match("#{$regex}#i", $url) &&
                    !array_key_exists(md5($url), self::$collected_urls) &&
                    !array_key_exists(md5($url), self::$collect_urls))
                {
                    echo util::colorize(date("H:i:s")." 发现列表网页：".$url."\n");
                    $link = array(
                        'url'           => $url,                            // 要抓取的URL
                        'url_type'      => 'list_page',                     // 要抓取的URL类型
                        'method'        => 'get',                           // 默认为"GET"请求, 也支持"POST"请求
                        'headers'       => self::$headers,                  // 此url的Headers, 可以为空
                        'data'          => array(),                         // 发送请求时需添加的参数, 可以为空
                        'context_data'  => '',                              // 此url附加的数据, 可以为空
                        'repeat'        => false,                           // 是否去重，true表示之前处理过的url也会插入待爬队列
                        'proxy'         => self::$configs['proxy'],         // 代理服务器
                        'proxy_auth'    => self::$configs['proxy_auth'],    // 代理验证
                        'collect_count' => 0,                               // 抓取次数
                        'collect_fails' => self::$configs['collect_fails'], // 允许抓取失败次数
                    );
                    // 放入爬虫队列
                    array_push(self::$collect_queue_links, $link);
                    // 放入抓取数组
                    self::$collect_urls[md5($url)] = time();
                    // 抓取队列数加1
                    self::$collect_url_num++;
                }
            }

            foreach (self::$configs['content_url_regexes'] as $regex) 
            {
                // 如果是内容链接
                // 不存在已爬取数组
                // 不存在爬取数组
                if (preg_match("#{$regex}#i", $url) &&
                    !array_key_exists($url, self::$collected_urls) &&
                    !array_key_exists($url, self::$collect_urls))
                {
                    echo util::colorize(date("H:i:s")." 发现内容网页：".$url."\n");
                    $link = array(
                        'url'           => $url,                            // 要抓取的URL
                        'url_type'      => 'content_page',                  // 要抓取的URL类型
                        'method'        => 'get',                           // 默认为"GET"请求, 也支持"POST"请求
                        'headers'       => self::$headers,                  // 此url的Headers, 可以为空
                        'data'          => array(),                         // 发送请求时需添加的参数, 可以为空
                        'context_data'  => '',                              // 此url附加的数据, 可以为空
                        'repeat'        => false,                           // 是否去重，true表示之前处理过的url也会插入待爬队列
                        'proxy'         => self::$configs['proxy'],         // 代理服务器
                        'proxy_auth'    => self::$configs['proxy_auth'],    // 代理验证
                        'collect_count' => 0,                               // 抓取次数
                        'collect_fails' => self::$configs['collect_fails'], // 允许抓取失败次数
                    );
                    // 放入爬虫队列
                    array_push(self::$collect_queue_links, $link);
                    // 放入抓取数组
                    self::$collect_urls[md5($url)] = time();
                    // 抓取队列数加1
                    self::$collect_url_num++;
                }
            }
        }
        echo "\n";
        //echo date("H:i:s")." 网页分析成功：".$collect_url."\n\n";
    }

    /**
     * 分析提取HTML页面中的字段
     * 父圈定范围，子取值，无法实现
     * 比如父用正则匹配出了一段HTML，子用xpath去提取，就会取不到值
     * 
     * @param mixed $html
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function get_html_fields($html, $url, $link, $page) 
    {

        $fields = array();

        foreach (self::$configs['fields'] as $conf) 
        {
            // 当前field抽取到的内容是否是有多项
            $repeated = isset($conf['repeated']) && $conf['repeated'] ? true : false;
            // 当前field抽取到的内容是否必须有值
            $required = isset($conf['required']) && $conf['required'] ? true : false;

            if (empty($conf['name'])) 
            {
                echo util::colorize(date("H:i:s") . " field的名字是空值, 请检查你的\"fields\"并添加field的名字\n\n", 'fail');
                exit;
            }

            // 如果没有定义抽取规则
            if (empty($conf['selector'])) 
            {
                $fields[$conf['name']] = array();
            }
            else 
            {
                // 没有设置抽取规则的类型 或者 设置为 xpath
                if (!isset($conf['selector_type']) || $conf['selector_type']=='xpath') 
                {
                    // 返回值一定是多项的
                    $parent_values = $this->get_fields_xpath($html, $conf['selector'], $conf['name']);
                }
                elseif ($conf['selector_type']=='regex') 
                {
                    $parent_values = $this->get_fields_regex($html, $conf['selector'], $conf['name']);
                }

                if (!empty($parent_values)) 
                {
                    $parent_values = $repeated ? $parent_values : $parent_values[0];

                    $child_fields = array();
                    if (!empty($conf['children'])) 
                    {
                        foreach ($conf['children'] as $child_conf) 
                        {
                            // 当前field抽取到的内容是否是有多项
                            $child_repeated = isset($child_conf['repeated']) && $child_conf['repeated'] ? true : false;
                            // 当前field抽取到的内容是否必须有值
                            $child_required = isset($child_conf['required']) && $child_conf['required'] ? true : false;

                            if (empty($child_conf['name'])) 
                            {
                                echo util::colorize(date("H:i:s") . " field的名字是空值, 请检查你的\"fields\"并添加field的名字\n\n", 'fail');
                                exit;
                            }

                            if (empty($child_conf['selector'])) 
                            {
                                $child_fields[$child_conf['name']] = array();
                            }
                            else 
                            {
                                // 父项抽取到的html作为子项的提取内容
                                $html = is_array($parent_values) ? implode("", $parent_values) : $parent_values;
                                if (!isset($child_conf['selector_type']) || $child_conf['selector_type']=='xpath') 
                                {
                                    $values = $this->get_fields_xpath($html, $child_conf['selector'], $child_conf['name']);
                                }
                                elseif ($child_conf['selector_type']=='regex') 
                                {
                                    $values = $this->get_fields_regex($html, $child_conf['selector'], $child_conf['name']);
                                }

                                if (!empty($values)) 
                                {
                                    $child_fields[$child_conf['name']] = $child_repeated ? $values : $values[0];
                                }
                            }

                            if (empty($child_fields[$child_conf['name']]) && $required) 
                            {
                                // 清空整个子项fields并且跳出foreach循环
                                $child_fields = array();
                                break;
                            }
                        }
                    }

                    if (!empty($child_fields)) 
                    {
                        foreach ($child_fields as $fieldname => $data) 
                        {
                            if ($this->on_extract_field) 
                            {
                                $return_data = call_user_func($this->on_extract_field, $conf['name'].'.'.$fieldname, $data, $page);
                                if (!isset($return_data))
                                {
                                    echo util::colorize(date("H:i:s") . " on_extract_field函数返回为空\n\n", 'warn');
                                }
                                else 
                                {
                                    // 有数据才会执行 on_extract_field 方法，所以这里不要被替换没了
                                    $child_fields[$fieldname] = $return_data;
                                }
                            }
                        }
                    }

                    // 这个子项判断父项需不需要重复是为了统一写法，并没有什么卵用
                    $child_fields = $repeated ? array($child_fields) : $child_fields;

                    // 有子项就存子项的数组，没有就存HTML块
                    $fields[$conf['name']] = empty($child_fields) ? $parent_values : $child_fields;
                }
            }
            if (empty($fields[$conf['name']]) && $required) 
            {
                // 清空整个fields并且跳出foreach循环
                $fields = array();
                break;
            }
        }

        if (!empty($fields)) 
        {
            foreach ($fields as $fieldname => $data) 
            {
                if ($this->on_extract_field) 
                {
                    $return_data = call_user_func($this->on_extract_field, $fieldname, $data, $page);
                    if (!isset($return_data))
                    {
                        echo util::colorize(date("H:i:s") . " on_extract_field函数返回为空\n\n", 'warn');
                    }
                    else 
                    {
                        // 有数据才会执行 on_extract_field 方法，所以这里不要被替换没了
                        $fields[$fieldname] = $return_data;
                    }
                }
            }

            if ($this->on_extract_page) 
            {
                $return_data = call_user_func($this->on_extract_page, $page, $fields);
                if (!isset($return_data))
                {
                    echo util::colorize(date("H:i:s") . " on_extract_page函数返回为空\n\n", 'warn');
                }
                elseif (!is_array($return_data))
                {
                    echo util::colorize(date("H:i:s") . " on_extract_page函数返回值必须是数组\n\n", 'warn');
                }
                else 
                {
                    $fields = $return_data;
                }
            }

            if (isset($fields) && is_array($fields)) 
            {
                self::$fields_num++;
                echo date("H:i:s")." 结果".self::$fields_num."：".json_encode($fields, JSON_UNESCAPED_UNICODE)."\n\n";

                // 如果设置了导出选项
                if (!empty(self::$configs['export'])) 
                {
                    self::$export_type = isset(self::$configs['export']['type']) ? self::$configs['export']['type'] : '';
                    if (self::$export_type == 'csv') 
                    {
                        util::put_file(self::$export_file, util::format_csv($fields)."\n", FILE_APPEND);
                    }
                    elseif (self::$export_type == 'sql') 
                    {
                        $sql = db::insert(self::$export_table, $fields, true);
                        util::put_file(self::$export_file, $sql.";\n", FILE_APPEND);
                    }
                    elseif (self::$export_type == 'db') 
                    {
                        db::insert(self::$export_table, $fields);
                    }
                }

            }
 
        }

    }

    /**
     * 采用xpath分析提取字段
     * 
     * @param mixed $html
     * @param mixed $selector
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function get_fields_xpath($html, $selector, $fieldname) 
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        //libxml_use_internal_errors(true);
        //$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        //$errors = libxml_get_errors();
        //if (!empty($errors)) 
        //{
            //print_r($errors);
            //exit;
        //}

        $xpath = new DOMXpath($dom);
        //$selector = "//*[@id='single-next-link']//div[contains(@class,'content')]/text()[1]";
        $elements = @$xpath->query($selector);
        //var_dump($elements);exit;
        if ($elements === false)
        {
            echo util::colorize(date("H:i:s") . "  field(\"{$fieldname}\")中selector的xpath(\"{$selector}\")语法错误\n\n", 'fail');
            exit;
        }

        $array = array();
        if (!is_null($elements)) 
        {
            foreach ($elements as $element) 
            {
                //var_dump($element);
                $nodeName = $element->nodeName;
                $nodeType = $element->nodeType;     // 1.Element 2.Attribute 3.Text
                // 如果是img标签，直接取src值
                if ($nodeType == 1 && in_array($nodeName, array('img'))) 
                {
                    $content = $element->getAttribute('src');
                }
                // 如果是标签属性，直接取节点值
                elseif ($nodeType == 2 || $nodeType == 3) 
                {
                    $content = $element->nodeValue;
                }
                else 
                {
                    // 保留nodeValue里的html符号，给children二次提取
                    $content = $dom->saveXml($element);
                    //$content = trim($dom->saveHtml($element));
                    $content = preg_replace(array("#^<{$nodeName}.*>#isU","#</{$nodeName}>$#isU"), array('', ''), $content);
                }
                $array[] = trim($content);
                //$nodes = util::node_to_array($dom, $element);
                //echo $nodes['@src']."\n";
                //echo "name: ".$element->nodeName."\n";
                //echo "value: ".$element->nodeValue."\n";
                //echo "attr: ".$element->getAttribute('src')."\n\n";
            }
        }
        return $array;
    }

    /**
     * 采用正则分析提取字段
     * 
     * @param mixed $html
     * @param mixed $selector
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function get_fields_regex($html, $selector, $fieldname) 
    {
        if(@preg_match_all($selector, $html, $out) === false)
        {
            echo util::colorize(date("H:i:s") . "  field(\"{$fieldname}\")中selector的regex(\"{$selector}\")语法错误\n\n", 'fail');
            exit;
        }

        $array = array();
        if (!is_null($out[1])) 
        {
            foreach ($out[1] as $v) 
            {
                $array[] = trim($v);
            }
        }
        return $array;
    }

    /**
     * 采用CSS选择器提取字段
     * 
     * @param mixed $html
     * @param mixed $selector
     * @param mixed $fieldname
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function get_fields_css($html, $selector, $fieldname) 
    {
    }
}

