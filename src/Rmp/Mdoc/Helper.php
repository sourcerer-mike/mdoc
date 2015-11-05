<?php


namespace Rmp\Mdoc;


class Helper {
	public static function basenameToTitle( $fileBasename ) {
		$fileBasename = basename($fileBasename);

		$title = strtr(
			trim($fileBasename),
			[
				'-' => ' ',
				'_' => ': '
			]
		);

		return $title;
	}
}