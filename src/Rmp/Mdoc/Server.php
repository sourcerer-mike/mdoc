<?php


namespace Rmp\Mdoc;

class Server {
	public function serve() {
		$path = RMP_MDOC_WORKING_DIR;

		if ( ! is_readable( $path ) ) {
			throw new \Exception(
				'Can not read from target.'
			);
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
}