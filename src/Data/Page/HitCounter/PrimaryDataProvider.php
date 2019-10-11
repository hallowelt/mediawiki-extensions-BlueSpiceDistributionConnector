<?php

namespace BlueSpice\DistributionConnector\Data\Page\HitCounter;

use Hooks;
use Title;
use BlueSpice\Data\ReaderParams;
use BlueSpice\Data\Page\PrimaryDataProvider as PageDataProvider;

class PrimaryDataProvider extends PageDataProvider {

	/**
	 *
	 * @var ReaderParams
	 */
	protected $readerParams = null;

	/**
	 *
	 * @return array
	 */
	protected function getTableNames() {
		return [ Schema::TABLE_NAME, Schema::TABLE_NAME_JOIN ];
	}

	/**
	 *
	 * @param \stdClass $row
	 * @return null
	 */
	protected function appendRowToData( $row ) {
		$title = Title::newFromRow( $row );
		if ( !$title || !$this->userCanRead( $title ) ) {
			return;
		}

		$fields = [ Record::ID, Record::NS, Record::TITLE, Record::IS_REDIRECT,
			Record::ID_NEW, Record::TOUCHED, Record::LATEST, Record::CONTENT_MODEL,
			Record::COUNTER ];
		$data = [];
		foreach ( $fields as $key ) {
			$data[ $key ] = $row->{$key};
		}
		$record = new Record( (object)$data );
		Hooks::run( 'BSPageStoreDataProviderBeforeAppendRow', [
			$this,
			$record,
			$title,
		] );
		if ( !$record ) {
			return;
		}
		$this->data[] = $record;
	}

	/**
	 *
	 * @return array
	 */
	protected function getDefaultConds() {
		return [ Record::CONTENT_MODEL => [ 'wikitext', '' ] ];
	}

	/**
	 *
	 * @param ReaderParams $params
	 */
	protected function getJoinConds( ReaderParams $params ) {
		$prefix = $this->context->getConfig()->get( 'DBprefix' );
		return [
			$this->getTableNames()[0] => [ "RIGHT OUTER JOIN", [
				"$prefix{$this->getTableNames()[0]}.page_id = $prefix{$this->getTableNames()[1]}.page_id"
			] ]
		];
	}

}