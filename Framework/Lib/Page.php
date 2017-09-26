<?php
namespace Core\Lib;
/**
 * 分页类
 * @author kevin
 *
 */
class Page {
    private $page_name = "p";
    private $total_num = 1;
    private $page_url = "";
    private $each_num = 10;
    private $now_page = 1;
    private $total_page = 1;
    private $style = 2;
    private $ajax = 0;
    private $pre_home = "";
    private $pre_last = "";
    private $pre_page = "";
    private $next_page = "";
    private $left_html = "<li>";
    private $right_html = "</li>";
    private $left_current_html = "<li>";
    private $right_current_html = "</li>";
    private $left_ellipsis_html = "<li>";
    private $right_ellipsis_html = "</li>";
    private $left_inside_a_html = "";
    private $right_inside_a_html = "";

    public function __construct($count=FALSE,$page=0,$pagesize=10,$style='1') {
        $this->pre_home = '首页';
        $this->pre_last = '尾页';
        $this->pre_page = '上一页';
        $this->next_page = '下一页';
        $this->next_group='换一批';
        if($page){
            $this->setNowPage($page);
        }else {
            if(isset($_GET[$this->page_name])){
                $this->setNowPage($_GET[$this->page_name]);
            }else {
                $this->setNowPage(0);
            }
        }
        $this->setPageUrl();
        if (is_numeric($count)){
            $this->setEachNum($pagesize);
            $this->setTotalNum($count);
            
            $this->setStyle($style);
        }
    }

    public function get($key) {
        return $this->$key;
    }

    public function set($key, $value) {
        return $this->$key = $value;
    }

    public function setPageName($page_name) {
        $this->page_name = $page_name;
        return TRUE;
    }

    public function setNowPage($page) {
        $this->now_page = 0 < intval($page) ? intval($page) : 1;
        return TRUE;
    }

    public function setEachNum($num) {
        $this->each_num = 0 < intval($num) ? intval($num) : 10;
        return TRUE;
    }

    public function setStyle($style) {
        $this->style = $style == "admin" ? 2 : $style;
        return TRUE;
    }

    public function setTotalNum($total_num) {
        $this->total_num = $total_num;
        return TRUE;
    }

    public function getNowPage() {
        return $this->now_page;
    }

    public function getTotalPage() {
        return $this->total_page;
    }

    public function getTotalNum() {
        return $this->total_num;
    }

    public function getEachNum() {
        return $this->each_num;
    }

    public function getLimitStart() {
        if ($this->getNowPage() == 1) {
            $tmp = 0;
            return $tmp;
        }
        $tmp = ($this->getNowPage() - 1) * $this->getEachNum();
        return $tmp;
    }

    public function getLimitEnd() {
        $tmp = $this->getNowPage() * $this->getEachNum();
        if ($this->getTotalNum() < $tmp) {
            $tmp = $this->getTotalNum();
        }
        return $tmp;
    }

    public function setTotalPage() {
        $this->total_page = ceil($this->getTotalNum() / $this->getEachNum());
    }

    public function show($style = 4) {
        $this->setTotalPage();
        if (!is_null($style)) {
            $this->style = $style;
        }
        $html_page = "";
        switch ($this->style) {
            case "1":
                $this->left_current_html = "<li><span class=\"currentpage\">";
                $this->right_current_html = "</span></li>";
                $this->left_inside_a_html = "<span>";
                $this->right_inside_a_html = "</span>";
                $html_page.= "<ul>";
                if ($this->getNowPage() == 1) {
                    $html_page.= "<li>" . $this->left_inside_a_html . $this->pre_page . $this->right_inside_a_html . "</li>";
                } else {
                    $html_page.= ("<li><a class=\"demo\" href=\"" . $this->page_url . ($this->getNowPage() - 1)) . "\">" . $this->left_inside_a_html . $this->pre_page . $this->right_inside_a_html . "</a></li>";
                }
                if ($this->getNowPage() == $this->getTotalPage() || $this->getTotalPage() == 0) {
                    $html_page.= "<li>" . $this->left_inside_a_html . $this->next_page . $this->right_inside_a_html . "</li>";
                } else {
                    $html_page.= ("<li><a class=\"demo\" href=\"" . $this->page_url . ($this->getNowPage() + 1)) . "\">" . $this->left_inside_a_html . $this->next_page . $this->right_inside_a_html . "</a></li>";
                }
                $html_page.= "</ul>";
                break;

            case "2":
                $this->left_current_html = "<li><span class='currentpage'>";
                $this->right_current_html = "</span></li>";
                $html_page.= "<ul>";
                if ($this->getNowPage() == 1) {
                    $html_page.= '<li> <span>' . $this->pre_home .  '</span></li>';
                    $html_page.= '<li> <span>' .  $this->pre_page . '</span></li>';
                } else {
                    $html_page.= '<li><a  href="' . $this->page_url . '1"> <span>'  . $this->pre_home  . '</span></a></li>';
                    $html_page.= '<li><a  href="' . $this->page_url . ($this->getNowPage() - 1) . '"> <span>'  . $this->pre_page  . "</span></a></li>";
                }
                $html_page.= $this->getNowBar();
                if ($this->getNowPage() == $this->getTotalPage() || $this->getTotalPage() == 0) {
                    $html_page.= '<li> <span>'  . $this->next_page . '</span></li>';
                    $html_page.= '<li> <span>' . $this->pre_last . '</span></li>';
                } else {
                    $html_page.= '<li><a  href="' . $this->page_url . ($this->getNowPage() + 1) . '"> <span>' . $this->next_page . '</span></a></li>';
                    $html_page.= '<li><a  href="' . $this->page_url . $this->getTotalPage() . '"> <span>' .$this->pre_last  . '</span></a></li>';
                }
                $html_page.= '</ul>';
                break;

            case "3":
                $html_page = '<span class="text">';
                //输出当前页与总页数
                
                if ($this->getTotalPage() == 1) {
                    $html_page .= '<i>'.$this->now_page.'</i></span><a class="prev prev_disabled"></a><a class="next"></a>';
                    //$html_page.= "<li>" . $this->left_inside_a_html . $this->pre_page . $this->right_inside_a_html . "</li>";
                } else {
                    $html_page .= '<i>'.$this->now_page.'</i>/'.$this->total_page.'</span><a href="' . $this->page_url . ($this->getNowPage() - 1) .'" class="prev prev_disabled"></a><a ';
                    if ($this->getNowPage() == $this->getTotalPage() || $this->getTotalPage() == 0) {
                        $html_page.= ' class="next"></a>';
                    } else {
                        $html_page.= ' href="' . $this->page_url . ($this->getNowPage() + 1).'" class="next"></a>';
                    }
                }
                break;
            case "4":
                $res['curr']  = $this->now_page;
                $res['total'] = $this->total_num;
                $res['url']   = $this->page_url;               
                $res['each']  = $this->each_num;
                $res['total_page'] = $this->total_page;
                if($this->total_page == $this->now_page){
                    $res['next_url'] =  $this->page_url.'1';
                }else {
                    $res['next_url'] =  $this->page_url.($this->getNowPage()+1);
                }
                $html_page = json_encode($res);
                break;
		    case "5":
                $this->left_current_html = "<li><span class=\"currentpage\">";
                $this->right_current_html = "</span></li>";
                $this->left_inside_a_html = "<span>";
                $this->right_inside_a_html = "</span>";
                $html_page.= "<ul>";
                if ($this->getNowPage() == 1) {
                    //$html_page.= "<li>" . $this->left_inside_a_html . $this->pre_home . $this->right_inside_a_html . "</li>";
                } else {
                    $html_page.= "<li><a class=\"demo\" href=\"#top\"
 onclick=\"javascript:goto(1)\">" . $this->left_inside_a_html . $this->pre_home . $this->right_inside_a_html . "</a></li>";
				    $html_page.= "<li><a class=\"demo\" href=\"#top\" onclick=\"javascript:goto(".($this->getNowPage() - 1).")". "\">" . $this->left_inside_a_html . $this->pre_page . $this->right_inside_a_html . "</a></li>";
     			}
			
                $html_page.= $this->getNowBarJs();
                if ($this->getNowPage() == $this->getTotalPage() || $this->getTotalPage() == 0) {
                    //$html_page.= "<li>" . $this->left_inside_a_html . $this->next_page . $this->right_inside_a_html . "</li>";
                    //$html_page.= "<li>" . $this->left_inside_a_html . $this->pre_last . $this->right_inside_a_html . "</li>";
                } else {
                    $html_page.= "<li><a class=\"demo\" href=\"#top\" onclick=\"javascript:goto(".($this->getNowPage() + 1).")". "\">" . $this->left_inside_a_html . $this->next_page . $this->right_inside_a_html . "</a></li>";
                    $html_page.= "<li><a class=\"demo\" href=\"#top\" onclick=\"javascript:goto(".($this->getTotalPage() ).")". "\">" . $this->left_inside_a_html . $this->pre_last . $this->right_inside_a_html . "</a></li>";
                }
                $html_page.= "</ul>";
		
                break;
            case "6":
                if($this->getNowPage()==$this->getTotalPage() && $this->getTotalPage()!=1){
                    $html_page= '<li><a class="dd" href="' . $this->page_url . (1) . "#page\">" . $this->left_inside_a_html . $this->next_group . $this->right_inside_a_html . "</a></li>";
                }else{
                    if($this->getTotalPage()==1){
                        $html_page = '';
                    }else{
                        $html_page= "<li><a class=\"\" href=\"" . $this->page_url . ($this->getNowPage() + 1) . "#page\">" . $this->left_inside_a_html . $this->next_group . $this->right_inside_a_html . "</a></li>";
                    }
                }
                break;
            case "7":
                $res['curr']  = $this->now_page;
                $res['total'] = $this->total_num;
                $res['url']   = $this->page_url;               
                $res['each']  = $this->each_num;
                $res['total_page'] = $this->total_page;
                if($this->total_page == $this->now_page){
                    $res['next_url'] =  $this->page_url.'1';
                }else {
                    $res['next_url'] =  $this->page_url.($this->getNowPage()+1);
                }
                $html_page = $res;
                break;
             case 8:
                 if($this->now_page == 1){
                     $html_page = '<a class="prev"></a>';
                 }else {
                     $html_page = '<a href="'.$this->page_url.($this->getNowPage()-1).'" class="prev"></a>';
                 }
                 $html_page.= $this->getBar();
                 if ($this->getNowPage() == $this->getTotalPage() || $this->getTotalPage() == 0) {
                     $html_page.= '<a class="next"></a>';
                 } else {
                     $html_page.= '<a href="' . $this->page_url . ($this->getNowPage() + 1) . '" class="next"></a>';
                 }
                 $html_page .= '<form method="get" action="'.$this->page_url.'"><p class="page_act"> 转到第<input class="text_ipt" name="'.$this->page_name.'" type="text" />页<button class="page_btn">确定</button></p></form>';
				 break;
			case 9:
                 if($this->now_page == 1){
                     $html_page = '<a class="prev"></a>';
                 }else {
                     $html_page = '<a href="'.$this->page_url.($this->getNowPage()-1).'" class="prev"></a>';
                 }
                 $html_page.= $this->getBar();
                 if ($this->getNowPage() == $this->getTotalPage() || $this->getTotalPage() == 0) {
                     $html_page.= '<a class="next"></a>';
                 } else {
                     $html_page.= '<a href="' . $this->page_url . ($this->getNowPage() + 1) . '" class="next"></a>';
                 }
                 //$html_page .= '<form method="get" action="'.$this->page_url.'"><p class="page_act"> 转到第<input class="text_ipt" name="'.$this->page_name.'" type="text" />页<button class="page_btn">确定</button></p></form>';
				 break;
             case 10:
                 if($this->now_page == 1){
                     $html_page = '<a class="prev"></a>';
                 }else {
                     $html_page = '<a href="'.$this->page_url.($this->getNowPage()-1).'" class="prev"></a>';
                 }
                 $html_page.= $this->getBar();
                 if ($this->getNowPage() == $this->getTotalPage() || $this->getTotalPage() == 0) {
                     $html_page.= '<a class="next"></a>';
                 } else {
                     $html_page.= '<a href="' . $this->page_url . ($this->getNowPage() + 1) . '" class="next"></a>';
                 }
                 $html_page .= '<form method="get" action="'.$this->page_url.'"><p class="page_act"><span class="total_page">总共<span id="page_cnt">'.$this->getTotalPage().'</span>页</span> 转到第<input class="text_ipt" name="'.$this->page_name.'" type="text" />页<button class="page_btn">确定</button></p></form>';
                 break;

        }
        return $html_page;
    }

	 private function getNowBarJs() {
        if (7 <= $this->getNowPage()) {
            $begin = $this->getNowPage() - 2;
        } else {
            $begin = 1;
        }
        if ($this->getNowPage() + 5 < $this->getTotalPage()) {
            $end = $this->getNowPage() + 5;
        } else {
            $end = $this->getTotalPage();
        }
        $result = "";
        if (1 < $begin) {
            $result.= $this->setPageHtmlJs(1, 1) . $this->setPageHtmlJs(2, 2);
            $result.= $this->left_ellipsis_html . "<span>...</span>" . $this->right_ellipsis_html;
        }
        $i = $begin;
        for (; $i <= $end; ++$i) {
            $result.= $this->setPageHtmlJs($i, $i);
        }
        if ($end < $this->getTotalPage()) {
            $result.= $this->left_ellipsis_html . "<span>...</span>" . $this->right_ellipsis_html;
        }
        return $result;
    }
	
    private function getNowBar() {
        if (7 <= $this->getNowPage()) {
            $begin = $this->getNowPage() - 2;
        } else {
            $begin = 1;
        }
        if ($this->getNowPage() + 5 < $this->getTotalPage()) {
            $end = $this->getNowPage() + 5;
        } else {
            $end = $this->getTotalPage();
        }
        $result = "";
        if (1 < $begin) {
            $result.= $this->setPageHtml(1, 1) . $this->setPageHtml(2, 2);
            $result.=  "<li><span>...</span></li>";
        }
        $i = $begin;
        for (; $i <= $end; ++$i) {
            $result.= $this->setPageHtml($i, $i);
        }
        if ($end < $this->getTotalPage()) {
            $result.= "<li><span>...</span></li>";
        }
        return $result;
    }
    private function getBar() {
        if (7 <= $this->getNowPage()) {
            $begin = $this->getNowPage() - 2;
        } else {
            $begin = 1;
        }
        if ($this->getNowPage() + 5 < $this->getTotalPage()) {
            $end = $this->getNowPage() + 5;
        } else {
            $end = $this->getTotalPage();
        }
        $result = "";
        if (1 < $begin) {
            $result.= $this->setHtml(1, 1) . $this->setHtml(2, 2);
            $result.=  "<a class='num'><span>...</span></a>";
        }
        $i = $begin;
        for (; $i <= $end; ++$i) {
            $result.= $this->setHtml($i, $i);
        }
        if ($end < $this->getTotalPage()) {
            $result.= "<a class='num'><span>...</span></a>";
        }
        return $result;
    }
	private function setPageHtmlJs($page_name, $page) {
        if ($this->getNowPage() == $page) {
            $result = $this->left_current_html . $page . $this->right_current_html;
            return $result;
        }
        $result = $this->left_html . "<a class='demo' href=\"#top\" onclick=\"javascript:goto(".($page_name ).")". "\">" . $this->left_inside_a_html . $page_name . $this->right_inside_a_html . "</a>" . $this->right_html;
        return $result;
    }
    private function setPageHtml($page_name, $page) {
        if ($this->getNowPage() == $page) {
            $result = '<li><span class="currentpage">' . $page. '</span></li>';
            return $result;
        }
        $result =  '<li><a href="' . $this->page_url . $page . '"><span>' . $page_name .'</span></a></li>';
        return $result;
    }
    private function setHtml($page_name, $page) {
        if ($this->getNowPage() == $page) {
            $result = '<a class="num active"><span>' . $page. '</span></a>';
            return $result;
        }
        $result =  '<a  class="num" href="' . $this->page_url . $page . '"><span>' . $page_name .'</span></a>';
        return $result;
    }

    private function setPageUrl() {
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
        }else if (isset($_SERVER['argv'])) {
            $uri = $_SERVER['PHP_SELF'] . "?" . $_SERVER['argv'][0];
        }else {
            $uri = $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];
        }
        $GLOBALS['_SERVER']['REQUEST_URI'] = $uri;
        if (empty($_SERVER['QUERY_STRING'])) {
            $this->page_url = $_SERVER['REQUEST_URI'] . "?" . $this->page_name . "=";
            return TRUE;
        }
        if (stristr($_SERVER['QUERY_STRING'], $this->page_name . "=")) {
            $this->page_url = str_replace($this->page_name . "=" . $this->now_page, "", $_SERVER['REQUEST_URI']);
            $last = $this->page_url[strlen($this->page_url) - 1];
            if ($last == "?" || $last == "&") {
                $this->page_url.= $this->page_name . "=";
                return TRUE;
            }
            $this->page_url.= "&" . $this->page_name . "=";
            return TRUE;
        }
        $this->page_url = $_SERVER['REQUEST_URI'] . "&" . $this->page_name . "=";
        return TRUE;
    }
}