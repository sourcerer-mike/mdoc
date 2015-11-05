<?php


namespace Rmp\Mdoc;

use Rmp\Mdoc\Md\Converter;
use Rmp\Mdoc\Md\Merge;
use Symfony\Component\Finder\Finder;

class Server {
	public function serve() {
		$path = RMP_MDOC_WORKING_DIR;

		if ( ! is_readable( $path ) ) {
			throw new \Exception(
				'Can not read from target.'
			);
		}

		if ( pathinfo( $_SERVER['REQUEST_URI'], PATHINFO_EXTENSION ) == 'md' ) {
			return $this->serveMarkdown( RMP_MDOC_WORKING_DIR );
		}

		return $this->serveDirectory( $path );

		throw new \DomainException(
			'Can not display files.'
		);
	}

	private function serveDirectory( $path ) {
		$allowedExtensions = [
			'md'
		];

		$targets = [ ];

		$list = glob( $path . '/*' );
		natcasesort( $list );

		foreach ( $list as $entry ) {
			if ( is_dir( $entry ) && file_exists( $entry . '.md' ) ) {
				continue;
			}

			$type = pathinfo( $entry, PATHINFO_EXTENSION );
			if ( is_file( $entry ) && ! in_array( $type, $allowedExtensions ) ) {
				continue;
			}

			$targets[] = array(
				'path'  => $entry,
				'type'  => $type,
				'url'   => $_SERVER['REQUEST_URI'] . basename( $entry ),
				'title' => Helper::basenameToTitle( basename( $entry, '.' . $type ) ),
			);
		}

		require_once RMP_MDOC_BASE_DIR . '/templates/list.phtml';
	}


	public function serveMarkdown( $mdFile ) {
		$basePath = dirname( $mdFile ) . '/' . basename( $mdFile, '.md' );

		$source = new Finder();
		$source->append( [ $mdFile ] );

		if ( is_dir( $basePath ) ) {
			$children = new Finder();
			$children->in( $basePath )
			         ->name( '*.md' )
			         ->sortByName();

			$source->append( $children );
		}

		$merger  = new Merge();
		$content = $merger->mergeFiles( $source, $basePath );

		$converter = new Converter();
		$html      = $converter->toHtml( $content );

		EventManager::dispatch( 'preHeader' );

		header( 'Content-Type: text/html; charset=utf-8' );

		EventManager::dispatch( 'html', $html );
	}
}