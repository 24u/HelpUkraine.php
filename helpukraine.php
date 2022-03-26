<?php

// Usage:
// Either access this script directly or include it at tbe begining of your own page's PHP:
//
// include("helpukraine.php");
//
// A special Stop The War page will be displayed instead of your web content if:
//
// - this script is accessed diractly
// - the $force_help_ukraine variable is set to true prior to including this file
// - the ?helpukraine=1 parameter is added to the URL of your page
// - your page is accessed from an IP known to be physically located in Russia or Belarus
//
// This file is provided as open-source by 24U Software and licensed under the "GNU LGPLv3" License.
// For more information visit https://24usw.com/helpukraine
//

if (($_GET["helpukraine"] ?? 1) and (($force_help_ukraine ?? 0) or ($_GET["helpukraine"] ?? 0)
    or (basename($_SERVER['REQUEST_URI']) == basename(__FILE__))
    or in_array(helpukraine_get_ip_country(helpukraine_get_ip()), ["RU", "BY"])))
{
  helpukraine_show_the_webpage();
  exit;
}

function get_url($url)
{
  if (ini_get('allow_url_fopen'))
  {
    // The easiest way
    $result = @file_get_contents($url);
    if ($result == false)
    {
      $error = error_get_last();
      return json_encode(["status" => $error["message"]]);
    }
    return $result;
  }
  elseif (function_exists('curl_init'))
  {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    $result = curl_exec($curl);
    if ($result === false) $result = curl_getinfo($curl, CURLINFO_HTTP_CODE) . " " . curl_error($curl);
    curl_close($curl);
    return $result;
  }
  return json_encode(["status" => "Can't load external content. Please make sure you have either CURL installed or 'allow_url_fopen' enabled in your php.ini."]);
}

function helpukraine_get_ip()
{
	//Just get the headers if we can or else use the SERVER global
	if (function_exists('apache_request_headers'))
	{
		$headers = apache_request_headers();
	}
	else
	{
		$headers = $_SERVER;
	}
	//Get the forwarded IP if it exists
	if (array_key_exists('X-Forwarded-For', $headers) && filter_var( $headers['X-Forwarded-For'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
	{
		$the_ip = $headers['X-Forwarded-For'];
	}
	elseif (array_key_exists('HTTP_X_FORWARDED_FOR', $headers) && filter_var($headers['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
	{
		$the_ip = $headers['HTTP_X_FORWARDED_FOR'];
	}
	else
	{	
		$the_ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
	}
	return $the_ip;
}

function helpukraine_get_ip_country($ip)
{
  // This API is limited to 45 requests per minute
  $json = get_url("http://ip-api.com/json/$ip?fields=countryCode");
  $data = json_decode($json, true);
  return ($data["countryCode"] ?? "error");
  
  // Subscription-based alternative with free 15,000 requests per hour: https://freegeoip.app
}

function helpukraine_get_news(&$news_list_en, &$news_list_ru, &$news_articles, &$credits_en, &$credits_ru)
{
  $news_json = get_url("https://24usw.com/getuanews");
  $news_decoded = json_decode($news_json, true);
  $status = $news_decoded["status"] ?? "Invalid result: $news_json";
  $ru = [
    "source" => "Источник",
    "no_news" => "Новости не найдены",
    "error" => "Ошибка при получении новостей, повторите попытку позже",
    "via" => "через", 
    "Left" => "Левая",
    "Lean Left" => "Наклон влево",
    "Center" => "Центр",
    "Lean Right" => "Наклон вправо",
    "Right" => "Правая",
    "Mixed" => "Смешанная",
    "Bias" => "Рейтинг предвзятости СМИ AllSides"
    ];
  if ($status == "ok")
  {
    $articles = $news_decoded["articles"] ?? [];
    $credits_en = $news_decoded["credits_en"] ?? "";
    $credits_ru = $news_decoded["credits_ru"] ?? "";
    $news_list = "";
    $news_articles = "";
    $id = 0;
    if (empty($articles))
    {
      $news_list_en = "No news found";
      $news_list_ru = $ru["no_news"];
      return;
    }
    foreach ($articles as $article)
    {
      $id++;
      $author = empty($article["author"] ?? "") ? "" : ($article["author"] . " | ");
      $link = $article["link"] ?? "";
      $pubdate = $article["pubDate"] ?? "";
      $feed = $article["feed"] ?? "";
      
      $rating_en = $article["rating"] ?? "";
      $rating_ru = empty($rating_en) ? "" : $ru[$article["rating"]];
      if (!empty($rating_en)) $rating_en = "AllSides Media Bias Rating: " . $rating_en;
      if (!empty($rating_ru)) $rating_ru = $ru["Bias"] . ": " . $rating_ru;
      
      $rating_url = $article["rating_url"] ?? "";
      if (!empty($rating_url))
      {
        if (!empty($rating_en)) $rating_en = "<a href=\"$rating_url\" target=\"_blank\">$rating_en</a>";
        if (!empty($rating_ru)) $rating_ru = "<a href=\"$rating_url\" target=\"_blank\">$rating_ru</a>";
      }
      
      $source_en = "<a href=\"$link\" target=\"_blank\">$pubdate " . $author . ($article["source_en"] ?? "") . "</a>" .
        (empty($feed) ? "" : " via $feed") . (empty($rating_en) ? "" : (" | " . $rating_en));
      $source_ru = "<a href=\"$link\" target=\"_blank\">$pubdate " . $author . ($article["source_ru"] ?? "") . "</a>"  .
        (empty($feed) ? "" : " " . $ru["via"] . " $feed") . (empty($rating_ru) ? "" : (" | " . $rating_ru));
      $title_en = $article["title_en"] ?? "";
      $title_ru = $article["title_ru"] ?? "";
      $summary_en = $article["summary_en"] ?? "";
      $summary_ru = $article["summary_ru"] ?? "";
      $content_en = $article["content_en"] ?? "";
      $content_ru = $article["content_ru"] ?? "";
      
      if (!empty($title_en) and !empty($summary_en) and !empty($source_en))
      {
        if (empty($content_en))
        {
          $news_list_en .= "<p><strong><a href=\"$link\" target=\"_blank\">$title_en</a></strong><br>$summary_en<br>" .
                           "<i>$source_en</i></p>\n";
        }
        else
        {
          $news_list_en .= "<p><strong><a href=\"#\" onClick=\"showArticle('en$id')\">$title_en</a></strong><br>$summary_en " .
                           "<i><a href=\"#\" onClick=\"showArticle('en$id')\">&raquo; Read More</a></i><br>" .
                           "<i>$source_en</i></p>\n";
          $news_articles .= <<<ARTICLEENHTML
      
<div id="en$id" class="modal-background">
<div class="modal-content en">
<div class="modal-close" onClick="hideArticle()">&times;</div>
<div class="modal-article">
<h2>$title_en</h2>
<p>$content_en</p>
<p><i>$source_en</i></p>
</div>
</div>
</div>
      
ARTICLEENHTML;
        }
      }
      if (!empty($title_ru) and !empty($summary_ru) and !empty($source_ru))
      {
        if (empty($content_ru))
        {
          $news_list_ru .= "<p><strong><a href=\"$link\" target=\"_blank\">$title_ru</a></strong><br>$summary_ru<br>" .
                           "<i>$source_ru</i></p>\n";
        }
        else
        {
          $news_list_ru .= "<p><strong><a href=\"#\" onClick=\"showArticle('ru$id')\">$title_ru</a></strong><br>$summary_ru " .
                           "<i><a href=\"#\" onClick=\"showArticle('ru$id')\">&raquo; Читать далее</a></i><br>" .
                           "<i>$source_ru</i></p>\n";
          $news_articles .= <<<ARTICLERUHTML
      
<div id="ru$id" class="modal-background">
<div class="modal-content ru">
<div class="modal-close" onClick="hideArticle()">&times;</div>
<div class="modal-article">
<h2>$title_ru</h2>
<p>$content_ru</p>
<p><i>$source_ru</i></p>
</div>
</div>
</div>
      
ARTICLERUHTML;
        }
      }
    }
  }
  else
  {
    $news_list_en = "Error fetching news, please try again later ($status)<!--$news_json-->";
    $news_list_ru = $ru["error"] . " ($status)";
    $credits_en = "";
    $credits_ru = "";
  }
}

function helpukraine_show_the_webpage()
{
  // Generate a webpage with information about war in Ukraine that Russian and Belarusian people
  // are intentionally blocked access to by their governments
  
  helpukraine_get_news($news_list_en, $news_list_ru, $news_articles, $credits_en, $credits_ru);
  $self = (basename($_SERVER['REQUEST_URI']) == basename(__FILE__)) ? "/" : $_SERVER['PHP_SELF'];
  echo <<<HTMLPAGE
  
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" > 
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stop The War</title>
<style>
html, body { padding: 0; margin: 0; height: 100%; font-family: Arial, Helvetica, sans-serif; }
h1 { padding: 0; margin: 0 }
h2 { padding: 0; margin: 1em 0 0 0 }
p { margin: 1em 0 0 0 }
.en a:hover, .ru a:hover { text-decoration: underline }
.page { padding: 0; margin: 0; height: 100%; overflow: hidden }
.en { height: 50%; overflow: auto; color: #F9DD16; padding: 0; background: #3A75C4 }
.en a { color: #FFD500; text-decoration: none }
.ru { height: 50%; overflow: auto; color: #3A75C4; padding: 0; background: #F9DD16 }
.ru a { color: #005BBB; text-decoration: none }
.news { font-size: 80%; margin-top: 1em }
.content { padding: 1em }
.modal-background { display: none;  position: fixed;  z-index: 1; left: 0; top: 0; width: 100%; height: 100%; overflow: hidden;
  background-color: rgb(0,0,0); background-color: rgba(0,0,0,0.4) }
.modal-content { overflow: hidden; margin: 0; width: 90%; height: 90%; position: absolute; top: 50%; left: 50%;
  -ms-transform: translate(-50%, -50%); transform: translate(-50%, -50%) }
.modal-article { margin: 0; padding: 1em 0 1em 1em; height: 95%; overflow: auto }
.modal-article h2 { margin-top: 0 }
.modal-close { color: #aaa; float: right; font-size: 28px; font-weight: bold; padding: 0.25em 0.25em 0 0 }
.modal-close:hover, .modal-close:focus { color: #fff; text-decoration: none; cursor: pointer }
</style>
</head> 
<body> 
<div class="page">
<div class="en" lang="en">
<div class="content">
<h1>Stop The War!</h1>
<p>On the 24th of February 2022 Russian armed forces invaded Ukraine and started war against a sovereign country. The attack came from both Russia and Belarus. Thousands of Ukrainian and Russian soldiers and hundreds of Ukrainian civilians, including women and children, have already been killed.</p>
<p><strong>If you protest</strong> against the war, <strong>you may get arrested</strong> for up to 15 years. Even if you just say that attacking another country with weapons is a war, you may be prosecuted the same way.</p>
<p>But <strong>if you close your eyes</strong> and stay quiet, <strong>you will be an accomplice</strong> to the mass murderer who claims to be your president.</p>
<h2>What do you choose?</h2>
<p>If you're connecting from Russia or Belarus then your government is actively blocking your access to independent information sources. To help you to know what's really happening, here is an uncensored list of recent news from variety of sources:</p>
<div class="news">
$news_list_en
$credits_en
<p><a href="$self?helpukraine=0">Show the main website</a></p>
</div>
</div>
</div>
<div class="ru" lang="ru">
<div class="content">
<h1>Остановите войну! </h1>
<p>24 февраля 2022 года российские вооруженные силы вторглись в Украину и начали войну против суверенной страны. Нападение произошло как со стороны России, так и со стороны Беларуси. Тысячи украинских и российских солдат и сотни украинских гражданских лиц, включая женщин и детей, уже убиты.</p> <p>
<p><strong>Если вы протестуете</strong> против войны, <strong>вас могут арестовать</strong> на срок до 15 лет. Даже если вы просто скажете, что нападение на другую страну с оружием - это война, вас могут привлечь к ответственности таким же образом.</p>
<p>Но <strong>если вы закроете глаза</strong> и будете молчать, <strong>вы станете сообщником</strong> массового убийцы, который называет себя вашим президентом.</p>
<h2>Что вы выберете? </h2>
<p>Если вы подключаетесь к Интернету из России или Беларуси, то ваше правительство активно блокирует ваш доступ к независимым источникам информации. Чтобы помочь вам узнать, что происходит на самом деле, вот список переведенных новостей из разных источников без цензуры:</p>
<div class="news">
$news_list_ru
$credits_ru
<p><a href="$self?helpukraine=0">Показать основной веб-сайт</a></p>
</div>
</div>
</div>
$news_articles
</div>
<script>
var current_article = null;
function showArticle(id)
{
  article_div = document.getElementById(id);
  if (current_article == id) return;
  if (current_article) current_article.style.display = "none";
  if (article_div)
  {
    current_article = article_div;
    article_div.style.display = "block";
  }
}
function hideArticle()
{
  if (current_article) current_article.style.display = "none";
  current_article = null;
}
window.onclick = function(event) {
  if (current_article && (event.target == current_article)) {
    current_article.style.display = "none";
  }
}
</script>
</body> 
</html>
  
HTMLPAGE;
}

?>