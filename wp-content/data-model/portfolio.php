<?php

include_once("common.php");

if (empty($method)) {
    msgErr("Не указан метод");
    exit;
}

if ($method == "get_portfolio_project") {
    if (empty($url)) {
        $url = '/portfolio/web-sites/181/';
    }
    if (empty($project_id)) {
        $url = '/portfolio/web-sites/181/';
    } else {
        $url = '/portfolio/web-sites/'.$project_id;
    }
    print_r(get_portfolio_project('http://goodsol.ru', $url, true));
} else if ($method == "get_portfolio_list") {
    if (empty($url)) {
        $url = '/portfolio/web-sites';
    }
    print_r(get_portfolio_list('http://goodsol.ru', $url));
} else
    msgErr("Указан не верный метод");

function get_portfolio_list($dom, $url) {
# получение списка портфолио проектов
# $dom - домен, который парсим
# $url - страница, которую парсим. Начинвется с "/", заканчивается без "/"
# возвращает массив значений в виде JSON

    $html = request($dom.$url);

    $pattern_id = '(.+)';
    $pattern_url = 'href="(.*\/.+\/.+\/'.$pattern_id.'\/)"';
    $pattern_img = '<img class="img"[\W|\w|\d|.]+src="(.*)".*\/>';
    $pattern_title = 'title="([\W|\w|\d|.]*)"';
    $pattern_span = '<span class="desc">([\W|\w|\d|.]*)<\/span>';
    $pattern = '#<a class="portfolio-element".+'.$pattern_url.'.+'.$pattern_title.'[\W|\w|\d|.]+'.$pattern_img.'[\W|\w|\d|.]+'.$pattern_span.'[\W|\w|\d|.]+<\/a>#Ui';
//    var_dump($pattern);

    $arr[] = array();
    if (preg_match_all($pattern, $html, $matches)) {
//    var_dump($matches);
        $t_arr = array();
        $r = 0;
        $c = 0;

        foreach ($matches as $keys) {
            $r = 0;
            if ($c == 0) {
                $c++;
                continue;
            }
            foreach ($keys as $values) {
                //print_r($matches[$c][$r]);
                $t_arr[$r][$c - 1] = $matches[$c][$r];
                $r++;
            }
            $c++;
        }

        $arr = array();
        foreach ($t_arr as $value) {
            $arr[] = array(
                'id' => $value[1],
                'url' => $value[0],
                'title' => str_replace(array("\r", "\t", "\n"), "", $value[2]),
                'img' => $value[3],
                'span' => str_replace(array("\r", "\t", "\n"), "", $value[4]),
//                'content' => ""
                'content' => get_portfolio_project($dom, $value[0])
            );
        }
        unset($t_arr);

//        var_dump($arr);
//        msgJSON($arr);
        return okJSON($arr);
    } else {
        return errorJSON("Ни одного проекта не найдено");
    }
}

function get_portfolio_project($dom, $url, $needOK = false) {
# получение списка портфолио проектов
# $dom - домен, который парсим
# $url - страница, которую парсим. Начинвется с "/" от корня сайта
# возвращает массив значений в виде JSON

    $html = request($dom.$url);
    $html = str_replace('src="/','src="'.$dom.'/', $html);

    $pattern_title = '<h1 style="font-size[\W|\w|\d|.]+">([\W|\w|\d|.]*)<\/h1>';
    $pattern_date = '<span style="font-size[\W|\w|\d|.]+">([\W|\w|\d|.]*)<\/span>';
    $pattern_URL = '<a style[\W|\w|\d|.]+">([\W|\w|\d|.]*)<\/a>';
    $pattern_descr = '<div itemprop="description">([\W|\w|\d|.]*)<\/div>[\W|\w|\d|.]*<div style="border-top';
    $pattern_content = '<div class="content"[\W|\w|\d|.]+'.$pattern_title.'[\W|\w|\d|.]+'.$pattern_date.'[\W|\w|\d|.]+'.$pattern_URL.'[\W|\w|\d|.]+'.$pattern_descr;
    $pattern = '#<div id="content-page"[\W|\w|\d|.]+'.$pattern_content.'#Ui';
//    var_dump($pattern);

    $arr = array();
    if (!($needOK) && (empty($needContent))) {
        return $arr;
        exit;
    }

    if (preg_match($pattern, $html, $matches)) {
//        var_dump($matches);
        $arr[] = array(
            'title' => str_replace(array("\r", "\t", "\n"), "", $matches[1]),
            'date' => str_replace(array("\r", "\t", "\n"), "", $matches[2]),
            'url' => $matches[3],
            'description' => str_replace(array("\r", "\t", "\n"), "", $matches[4])
//            'description' => strip_tags(str_replace(array("\r", "\t", "\n"), "", $matches[4]))
        );
        if ($needOK)
            return okJSON($arr);
        else
//            return json_result($arr);
            return $arr;
    } else {
        if ($needOK)
            return errorJSON("Проект не найден");
        else
//            return json_result($arr);
            return $arr;
    }
}

function request($url){
	# получаем страницу по url
    $myCurl = curl_init();
	curl_setopt_array($myCurl,
		array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true, 
			CURLOPT_HEADER => false,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.1; ru-RU; rv:1.7.12) Gecko/20050919 Firefox/1.0.7"
		)
	);
	
	$curl_resp = curl_exec($myCurl);
	$error = curl_errno($myCurl);
	
	if (!$error){
		curl_close($myCurl);
		return $curl_resp;
		exit;
	} else {
		msgErr(curl_error($myCurl));
        curl_close($myCurl);
	}
    return false;
}

function parse($tag1, $tag2, $str, $search_pos, &$out_pos) {
# парсинг между двумя тегами
# search_pos - позиция откуда нужно начать поиск
# out_pos - позиция последнего найденного блока (амперанса означает что это не входящий, а выходящий параметр)
	$p = $search_pos;
	$p = strpos($str, $tag1, $p);
	$p2 = strpos($str, $tag2, $p);
	if ($p2 > $p) {
		$out_pos = $p2;
		return  substr($str, $p+strlen($tag1), $p2-$p-strlen($tag1));
	} else {
		$out_pos = 0;
		return "";
	}
}