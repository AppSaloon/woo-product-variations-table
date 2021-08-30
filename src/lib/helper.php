<?php

/**
 * Loops array one by one
 *
 * @param $array
 *
 * @return Generator
 *
 * @since 1.0.0
 */
function woo_pvt_generator( $array ) {
	foreach ( $array as $a => $b ) {
		yield $a => $b;
	}
}