<?php


namespace Rmp\Mdoc\Md;


class Converter {
	public function toHtml( $content ) {
		$environment = \League\CommonMark\Environment::createCommonMarkEnvironment();
		$environment->mergeConfig( [ ] );
		$environment->addExtension( new \Webuni\CommonMark\TableExtension\TableExtension() );

		$converter = new \League\CommonMark\Converter(
			new \League\CommonMark\DocParser( $environment ),
			new \League\CommonMark\HtmlRenderer( $environment )
		);

		$content = $converter->convertToHtml( $content );

		// strip comment marks
		$markdownContent = preg_replace(
			"/\n\-\-[\ ]*/s",
			"\n",
			$content
		);

		return $markdownContent;
	}
}