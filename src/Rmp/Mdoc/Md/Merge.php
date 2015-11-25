<?php


namespace Rmp\Mdoc\Md;


use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Merge {
	/**
	 * @param SplFileInfo[] $fileList
	 * @param string        $basePath
	 *
	 * @return string
	 */
	public function mergeFiles( $fileList, $basePath ) {
		$imagePath = str_replace( dirname(RMP_MDOC_BASE_DIR), '', $basePath );

		$contents = '';
		foreach ( $fileList->files() as $singleFile ) {
			if ( false == $singleFile instanceof SplFileInfo ) {
				$singleFile = new SplFileInfo( $singleFile->getRealPath(), $basePath, $basePath );
			}

			/** @var SplFileInfo $singleFile */
			$buffer = $singleFile->getContents() . PHP_EOL . PHP_EOL;

			$depth = max(
				0,
				substr_count(
					str_replace(
						$basePath,
						'',
						$singleFile->getRealPath()
					)
					,
					'/'
				) - 1 // correction because everything is in a sub-folder
			);

			$directory = $singleFile->getPath()
			             . '/' . $singleFile->getBasename( '.md' );

			if ( is_dir( $directory ) ) {
				$featureFinder = new Finder();
				$featureFinder->in( $directory )
				              ->name( '*.feature' )
				              ->depth( 0 );
				foreach ( $featureFinder->files() as $feature ) {
					$buffer .= $this->parseFeature( $feature );
				}
			}

			$link = ltrim(
				'http://' . $_SERVER['HTTP_HOST'] .
				str_replace(
					$_SERVER['DOCUMENT_ROOT'],
					'',
					$singleFile->getPath() . '/'
				)
				. '/',
				'/'
			);

			$buffer = str_replace( '](./', '](' . $link, $buffer );

			// in between
			$buffer = str_replace(
				"\n#",
				"\n#" . str_repeat( '#', $depth ),
				$buffer
			);

			// in beginning
			$buffer = preg_replace(
				'/^#/',
				str_repeat( '#', $depth + 1 ),
				$buffer
			);

			$contents .= $buffer;
		}

		return $contents;
	}

	public function mergeDirectory( $basePath ) {
		$finder = new Finder();
		$finder->in( $basePath )
		       ->name( '*.md' )
		       ->sortByName();

		return $this->mergeFiles( $finder, $basePath );
	}

	/**
	 * @param SplFileInfo $fileInfo
	 *
	 * @todo make it event driven
	 *
	 * @return mixed|string
	 *
	 */
	protected function parseFeature( $fileInfo ) {
		$feature = $fileInfo->getContents();

		$scenarios = explode( 'Szenario:', $feature );
		$feature   = array_shift( $scenarios );

		$lines = explode( PHP_EOL, $feature );

		if ( 0 === strpos( $feature, '#' ) ) {
			array_shift( $lines );
		}

		$title = array_shift( $lines );
		$title = str_replace( 'Funktionalität: ', '## ', $title );

		$userStory = trim( implode( PHP_EOL, $lines ) );
		$userStory = preg_replace( '/\n[\s]*/', "\n", $userStory );

		$imageFile = $fileInfo->getPath()
		             . '/' . $fileInfo->getBasename( '.feature' )
		             . '.png';

		$output = $title . PHP_EOL . PHP_EOL;
		$output .= $userStory . PHP_EOL . PHP_EOL;

		if ( is_readable( $imageFile ) ) {
			$output .= '![' . basename( $imageFile, '.png' ) . '](./'
			           . $fileInfo->getRelativePath()
			           . basename( $fileInfo->getPath() )
			           . '/' . basename( $imageFile )
			           . ')' . PHP_EOL . PHP_EOL;
		}

		foreach ( $scenarios as $scenario ) {
			$scenario = trim( $scenario );

			if ( ! $scenario ) {
				continue;
			}

			$output .= $this->parseScenario( $scenario, $fileInfo );
		}

		return $output;
	}

	/**
	 * @param string      $scenario
	 * @param SplFileInfo $fileInfo
	 *
	 * @todo make it event driven
	 *
	 * @return string
	 */
	public function parseScenario( $scenario, $fileInfo ) {
		$feature = '';

		list( $title, $body ) = explode(
			PHP_EOL,
			$scenario,
			2
		);

		$feature .= '### ' . $title
		            . PHP_EOL . PHP_EOL
		            . $body
		            . PHP_EOL . PHP_EOL;

		$path = $fileInfo->getPath() . '/' . $fileInfo->getBasename( '.feature' );

		if ( is_dir( $path ) ) {
			$targetFile = $path . '/' . $this->sanitizeFileName( $title ) . '.md';
			if ( is_readable( $targetFile ) ) {
				$feature .= file_get_contents( $targetFile )
				            . PHP_EOL . PHP_EOL;
			}
		}

		return $feature;
	}

	public function sanitizeFileName( $fileName ) {
		$sanitized = strtr(
			$fileName,
			[
				'Ä' => 'Ae',
				'Ö' => 'Oe',
				'Ü' => 'Ue',
				'ä' => 'ae',
				'ö' => 'oe',
				'ü' => 'ue',
			]
		);

		$sanitized = preg_replace( '/[^A-Za-z-]/', '-', $sanitized );

		return $sanitized;
	}
}