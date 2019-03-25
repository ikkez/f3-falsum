<?php

/**
 * Pretty error handling for the PHP Fat-Free Framework
 *
 * The contents of this file are subject to the terms of the GNU General
 * Public License Version 3.0. You may not use this file except in
 * compliance with the license. Any of the license terms and conditions
 * can be waived if you get permission from the copyright holder.
 *
 * Christian Knuth <ikkez0n3@gmail.com>
 * https://github.com/ikkez/f3-falsum
 *
 * @version 2.6.0
 * @date: 03.01.2017
 * @author: Rafael Santos, https://github.com/rafamds
 **/

namespace Falsum;

class Run {
	public static function handler($override=FALSE) {
		/*
		 * Create a framework instance variable.
		 */

		$fwi=\Base::instance();

		if ($override) {
			self::showErrors($fwi);
		} else {
			if ($fwi->get('DEBUG')==3) {
				self::showErrors($fwi);
			}
		}
	}

	public static function showErrors($fwi) {
		/*
		 * Set the ONERROR property.
		 */
		$fwi->set('ONERROR',
			function($fwi) {
				$resources=__DIR__.'/Resources/';

				// clear output buffer
				while(ob_get_level())
					ob_end_clean();

				/*
				 * CSS Files
				 */
				$railscast=file_get_contents($resources.'css/railscast.css');
				$styles=file_get_contents($resources.'css/style.css');

				/*
				 * JS Files
				 */
				$jquery=file_get_contents($resources.'js/jquery.js');
				$main=file_get_contents($resources.'js/main.js');

				$status=$fwi->get('ERROR.status');
				$code=$fwi->get('ERROR.code');
				$text=$fwi->get('ERROR.text');
				$trace=$fwi->get('ERROR.trace');

				preg_match_all("/\[.*:\d+\]/",strip_tags($trace),$matches);
				foreach ($matches[0] as $key=>$result) {
					$result=str_replace(['[',']'],'',$result);
					preg_match_all("/:\d+/",$result,$line);
					$errors[$key]['line']=str_replace(':','',$line[0][0]);
					$errors[$key]['file']=
						str_replace(':'.$errors[$key]['line'],'',$result);

					$eol='';
					$line=$errors[$key]['line']-1;
					$line_start=$line-6;
					$line_end=$line+6;
					$pos=0;

					$user_agent=$_SERVER['HTTP_USER_AGENT'];
					$os_array=[
						'/windows nt 10/i'=>'Windows',
						'/windows nt 6.3/i'=>'Windows',
						'/windows nt 6.2/i'=>'Windows',
						'/windows nt 6.1/i'=>'Windows',
						'/windows nt 6.0/i'=>'Windows',
						'/windows nt 5.2/i'=>'Windows',
						'/windows nt 5.1/i'=>'Windows',
						'/windows xp/i'=>'Windows',
						'/windows nt 5.0/i'=>'Windows',
						'/windows me/i'=>'Windows',
						'/win98/i'=>'Windows',
						'/win95/i'=>'Windows',
						'/win16/i'=>'Windows',
						'/macintosh|mac os x/i'=>'Mac OS X',
						'/mac_powerpc/i'=>'Mac OS 9',
						'/linux/i'=>'Linux',
						'/ubuntu/i'=>'Ubuntu',
						'/iphone/i'=>'iPhone',
						'/ipod/i'=>'iPod',
						'/ipad/i'=>'iPad',
						'/android/i'=>'Android',
						'/blackberry/i'=>'BlackBerry',
						'/webos/i'=>'Mobile',
					];

					foreach ($os_array as $regex=>$value) {
						if (preg_match($regex,$user_agent)) {
							$os_platform=$value;
						}
					}

					$add_root='';
					if ($os_platform!='Windows') {
						$add_root=$_SERVER['DOCUMENT_ROOT'].'/';
					}

					$rows=file($add_root.$errors[$key]['file']);
					$errors[$key]['script']='<div class="code-wrap">';
					$errors[$key]['script'].='<pre class="excerpt">'.$eol;
					for ($pos=$line_start;$pos<=$line_end;$pos++):
						$row=isset($rows[$pos])?$rows[$pos]:'';
						if ($pos==$line):
							$errors[$key]['script'].='<code class="error-line">'.$pos.' '.
								htmlentities($row).'</code>'.$eol;
						else:
							$errors[$key]['script'].='<code>'.$pos.' '.htmlentities($row).
								'</code>'.$eol;
						endif;
					endfor;
					$errors[$key]['script'].='</pre></div>';
					$key++;
				}

				$html_structure=''.
					'<html>'.
					'	<head>'.
					'		<style>'.$styles.'</style>'.
					'		<style>'.$railscast.'</style>'.
					'		<script type="text/javascript">'.$jquery.'</script>'.
					'		<script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/9.1.0/highlight.min.js"></script>'.
					'	</head>'.
					'	<body>'.
					'		<div id="container">'.
					'			<div class="header">'.
					'				<h1>'.$code.' '.$status.'</h1>'.
					'				<h2>'.$text.'</h2>'.
					'			</div>'.
					'			<div class="content">'.
					'				<div class="left"><div>'.
					'					<h3>Code Analysis</h3>';

				foreach ($errors as $key=>$error) {
					$selected=$key==0?' selected':'';
					$html_structure.=''.
						'<div class="code'.$selected.'" ref="'.$key.'">'.$error['script'].
						'</div>';
				}

				$html_structure.='<h3 class="headers">Headers</h3>';

				foreach ($fwi->get('HEADERS') as $key=>$value) {
					$html_structure.='<div class="variables"><span>'.$key.'</span> '.
						$value.'</div>';
				}

				$html_structure.=''.
					'				</div></div>'.
					'				<div class="right"><div>'.
					'					<h3>Error Stack</h3><div class="stacks">';

				foreach ($errors as $key=>$error) {
					$selected=$key==0?' selected':'';
					$path=substr($error['file'],-25);
					$html_structure.=''.
						'<div class="stack'.$selected.'" ref="'.$key.'">'.
						'	<h4><span class="pos">'.$key.'</span> Line Number '.
						($error['line']-1).'</h4>'.
						'	<p>...'.$path.'</p>'.
						'</div>';
				}

				$html_structure.=''.
					'				</div></div></div>'.
					'			</div>'.
					'		</div>'.
					'		<script type="text/javascript">'.$main.'</script>'.
					'	</body>'.
					'</html>';

				echo $html_structure;
			}
		);
	}
}
