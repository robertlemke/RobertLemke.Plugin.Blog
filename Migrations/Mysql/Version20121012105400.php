<?php
namespace TYPO3\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema,
	TYPO3\Flow\Persistence\Doctrine\Service;

/**
 * Adjust flow3 to flow
 */
class Version20121012105400 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

			// collect foreign keys pointing to "our" tables
		$tableNames = array(
			'robertlemke_plugin_blog_domain_model_blog',
			'robertlemke_plugin_blog_domain_model_category',
			'robertlemke_plugin_blog_domain_model_comment',
			'robertlemke_plugin_blog_domain_model_image',
			'robertlemke_plugin_blog_domain_model_post',
			'robertlemke_plugin_blog_domain_model_tag',
		);
		$foreignKeyHandlingSql = Service::getForeignKeyHandlingSql($schema, $this->platform, $tableNames, 'flow3_persistence_identifier', 'persistence_object_identifier');

			// drop FK constraints
		foreach ($foreignKeyHandlingSql['drop'] as $sql) {
			$this->addSql($sql);
		}

		foreach ($tableNames as $tableName) {
			$this->addSql("ALTER TABLE $tableName DROP PRIMARY KEY");
			$this->addSql("ALTER TABLE $tableName CHANGE flow3_persistence_identifier persistence_object_identifier VARCHAR(40) NOT NULL");
			$this->addSql("ALTER TABLE $tableName ADD PRIMARY KEY (persistence_object_identifier)");
		}

			// add back FK constraints
		foreach ($foreignKeyHandlingSql['add'] as $sql) {
			$this->addSql($sql);
		}
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

			// collect foreign keys pointing to "our" tables
		$tableNames = array(
			'robertlemke_plugin_blog_domain_model_blog',
			'robertlemke_plugin_blog_domain_model_category',
			'robertlemke_plugin_blog_domain_model_comment',
			'robertlemke_plugin_blog_domain_model_image',
			'robertlemke_plugin_blog_domain_model_post',
			'robertlemke_plugin_blog_domain_model_tag',
		);
		$foreignKeyHandlingSql = Service::getForeignKeyHandlingSql($schema, $this->platform, $tableNames, 'persistence_object_identifier', 'flow3_persistence_identifier');

			// drop FK constraints
		foreach ($foreignKeyHandlingSql['drop'] as $sql) {
			$this->addSql($sql);
		}

			// rename identifier fields
		foreach ($tableNames as $tableName) {
			$this->addSql("ALTER TABLE $tableName DROP PRIMARY KEY");
			$this->addSql("ALTER TABLE $tableName CHANGE persistence_object_identifier flow3_persistence_identifier VARCHAR(40) NOT NULL");
			$this->addSql("ALTER TABLE $tableName ADD PRIMARY KEY (flow3_persistence_identifier)");
		}

			// add back FK constraints
		foreach ($foreignKeyHandlingSql['add'] as $sql) {
			$this->addSql($sql);
		}
	}

}

?>