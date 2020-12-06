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
 * @version 2.8.1
 * @date: 06.012.2020
 * @author: Rafael Santos, https://github.com/rafamds
 **/

namespace Falsum;

class Run {

	/**
	 * initialize handler registration
	 * @param bool $force
	 */
	public static function handler($force=FALSE) {
		/** @var \Base $fw */
		$fw=\Base::instance();

		if ($force || $fw->get('DEBUG')==3)
			// register error handler in the framework
			$fw->set('ONERROR', 'Falsum\Run::handleError');
	}

	/**
	 * run error handler
	 * @param $fw
	 */
	public static function handleError(\Base $fw) {

		$resources=__DIR__.'/Resources/';

		// clear output buffer
		while(ob_get_level())
			ob_end_clean();

		// CSS files
		$railscast=file_get_contents($resources.'css/railscast.css');
		$styles=file_get_contents($resources.'css/style.css');

		// JS Files
		$jquery=file_get_contents($resources.'js/jquery.js');
		$main=file_get_contents($resources.'js/main.js');

		$status=$fw->get('ERROR.status');
		$code=$fw->get('ERROR.code');
		$text=$fw->get('ERROR.text');
		$trace=$fw->get('ERROR.trace');

		if (!$fw->devoid('EXCEPTION',$exception)) {
			$text = get_class($exception).': '.$text;
		}

		preg_match_all("/\[.*:\d+\]/",strip_tags($trace),$matches);

		if (!$exception)
			// drop first item, which is the error handler definition line
			if (!empty($matches[0]) && count($matches[0])>1)
				array_shift($matches[0]);

		$errors=[];

		foreach ($matches[0] as $key=>$result) {
			$result=str_replace(['[',']'],'',$result);
			preg_match_all("/:\d+/",$result,$line);
			if (!isset($errors[$key]))
				$errors[$key]=[];
			$errors[$key]['line']=str_replace(':','',$line[0][0]);
			$errors[$key]['file']=
				preg_replace("/(:".$errors[$key]['line']."|\(\d+\) : eval\(\)\'d code:".$errors[$key]['line'].")/",'',$result);

			$eol='';
			$line=$errors[$key]['line']-1;
			$line_start=$line-6;
			$line_end=$line+6;

			$filePath = $errors[$key]['file'];
			if (!is_file($filePath))
				$filePath=$fw->get('ROOT').'/'.$filePath;
			if (!is_file($filePath)) {
				$errors[$key]['script']='';
				continue;
			}
			$rows=file($filePath);
			$errors[$key]['script']='<div class="code-wrap">';
			$errors[$key]['script'].='<pre class="excerpt">'.$eol;
			for ($pos=$line_start;$pos<=$line_end;$pos++) {
				$row=isset($rows[$pos])?$rows[$pos]:'';
				if ($pos==$line) {
					$errors[$key]['script'].='<code class="error-line">'.$pos.' '.
						htmlentities($row).'</code>'.$eol;
				} else
					$errors[$key]['script'].='<code>'.$pos.' '.htmlentities($row).
						'</code>'.$eol;
			}
			$errors[$key]['script'].='</pre></div>';
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

		foreach ($fw->get('HEADERS') as $key=>$value) {
			$html_structure.='<div class="variables"><span>'.$key.'</span> '.
				$value.'</div>';
		}

		$html_structure.=
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

		$html_structure.=
			'				</div></div></div>'.
			'			</div>'.
			'		</div>'.
			'		<script type="text/javascript">'.$main.'</script>'.
			'	</body>'.
			'</html>';

		echo $html_structure;
	}
}
