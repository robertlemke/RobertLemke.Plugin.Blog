<?php
namespace TYPO3\FLOW3\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
	Doctrine\DBAL\Schema\Schema;

/**
 * Zero Migration for the Blog Plugin
 *
 * Note: if you are migrating from the TYPO3.Blog package, you can manually run these
 *       queries to reuse the existing data:
 *
 * RENAME TABLE typo3_blog_domain_model_blog TO robertlemke_plugin_blog_domain_model_blog;
 * RENAME TABLE typo3_blog_domain_model_category TO robertlemke_plugin_blog_domain_model_category;
 * RENAME TABLE typo3_blog_domain_model_post TO robertlemke_plugin_blog_domain_model_post;
 * RENAME TABLE typo3_blog_domain_model_post_relatedposts_join TO robertlemke_plugin_blog_domain_model_post_relatedposts_join;
 * RENAME TABLE typo3_blog_domain_model_post_tags_join TO robertlemke_plugin_blog_domain_model_post_tags_join;
 * RENAME TABLE typo3_blog_domain_model_comment TO robertlemke_plugin_blog_domain_model_comment;
 * RENAME TABLE typo3_blog_domain_model_image TO robertlemke_plugin_blog_domain_model_image;
 * RENAME TABLE typo3_blog_domain_model_tag TO robertlemke_plugin_blog_domain_model_tag;
 *
 */
class Version20120926094000 extends AbstractMigration {

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function up(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("CREATE TABLE robertlemke_plugin_blog_domain_model_tag (flow3_persistence_identifier VARCHAR(40) NOT NULL, name VARCHAR(20) NOT NULL, PRIMARY KEY(flow3_persistence_identifier)) ENGINE = InnoDB");
		$this->addSql("CREATE TABLE robertlemke_plugin_blog_domain_model_blog (flow3_persistence_identifier VARCHAR(40) NOT NULL, authorpicture VARCHAR(40) DEFAULT NULL, title VARCHAR(80) NOT NULL, description VARCHAR(150) NOT NULL, fulldescription VARCHAR(255) NOT NULL, keywords VARCHAR(255) NOT NULL, blurb TEXT NOT NULL, twitterusername VARCHAR(80) NOT NULL, googleanalyticsaccountnumber VARCHAR(20) NOT NULL, INDEX IDX_72A1EAD8A2B9E27 (authorpicture), PRIMARY KEY(flow3_persistence_identifier)) ENGINE = InnoDB");
		$this->addSql("CREATE TABLE robertlemke_plugin_blog_domain_model_category (flow3_persistence_identifier VARCHAR(40) NOT NULL, name VARCHAR(80) NOT NULL, UNIQUE INDEX flow3_identity_robertlemke_plugin_blog_domain_model_category (name), PRIMARY KEY(flow3_persistence_identifier)) ENGINE = InnoDB");
		$this->addSql("CREATE TABLE robertlemke_plugin_blog_domain_model_comment (flow3_persistence_identifier VARCHAR(40) NOT NULL, post VARCHAR(40) DEFAULT NULL, date DATETIME NOT NULL, author VARCHAR(80) NOT NULL, emailaddress VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, spam TINYINT(1) NOT NULL, INDEX IDX_AE0F05295A8A6C8D (post), UNIQUE INDEX flow3_identity_robertlemke_plugin_blog_domain_model_comment (date, author), PRIMARY KEY(flow3_persistence_identifier)) ENGINE = InnoDB");
		$this->addSql("CREATE TABLE robertlemke_plugin_blog_domain_model_image (flow3_persistence_identifier VARCHAR(40) NOT NULL, originalresource VARCHAR(40) DEFAULT NULL, title VARCHAR(100) NOT NULL, INDEX IDX_A252FA284E59BB9C (originalresource), PRIMARY KEY(flow3_persistence_identifier)) ENGINE = InnoDB");
		$this->addSql("CREATE TABLE robertlemke_plugin_blog_domain_model_post (flow3_persistence_identifier VARCHAR(40) NOT NULL, blog VARCHAR(40) DEFAULT NULL, image VARCHAR(40) DEFAULT NULL, category VARCHAR(40) DEFAULT NULL, title VARCHAR(100) NOT NULL, date DATETIME NOT NULL, author VARCHAR(50) NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_E83ED716C0155143 (blog), INDEX IDX_E83ED716C53D045F (image), INDEX IDX_E83ED71664C19C1 (category), UNIQUE INDEX flow3_identity_robertlemke_plugin_blog_domain_model_post (title, date), PRIMARY KEY(flow3_persistence_identifier)) ENGINE = InnoDB");
		$this->addSql("CREATE TABLE robertlemke_plugin_blog_domain_model_post_tags_join (blog_post VARCHAR(40) NOT NULL, blog_tag VARCHAR(40) NOT NULL, INDEX IDX_9084A1FABA5AE01D (blog_post), INDEX IDX_9084A1FA6EC3989 (blog_tag), PRIMARY KEY(blog_post, blog_tag)) ENGINE = InnoDB");
		$this->addSql("CREATE TABLE robertlemke_plugin_blog_domain_model_post_relatedposts_join (blog_post VARCHAR(40) NOT NULL, related_id VARCHAR(40) NOT NULL, INDEX IDX_59B5CBA8BA5AE01D (blog_post), INDEX IDX_59B5CBA84162C001 (related_id), PRIMARY KEY(blog_post, related_id)) ENGINE = InnoDB");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_blog ADD CONSTRAINT FK_72A1EAD8A2B9E27 FOREIGN KEY (authorpicture) REFERENCES typo3_flow3_resource_resource (flow3_persistence_identifier)");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_comment ADD CONSTRAINT FK_AE0F05295A8A6C8D FOREIGN KEY (post) REFERENCES robertlemke_plugin_blog_domain_model_post (flow3_persistence_identifier)");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_image ADD CONSTRAINT FK_A252FA284E59BB9C FOREIGN KEY (originalresource) REFERENCES typo3_flow3_resource_resource (flow3_persistence_identifier)");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post ADD CONSTRAINT FK_E83ED716C0155143 FOREIGN KEY (blog) REFERENCES robertlemke_plugin_blog_domain_model_blog (flow3_persistence_identifier)");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post ADD CONSTRAINT FK_E83ED716C53D045F FOREIGN KEY (image) REFERENCES robertlemke_plugin_blog_domain_model_image (flow3_persistence_identifier)");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post ADD CONSTRAINT FK_E83ED71664C19C1 FOREIGN KEY (category) REFERENCES robertlemke_plugin_blog_domain_model_category (flow3_persistence_identifier)");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post_tags_join ADD CONSTRAINT FK_9084A1FABA5AE01D FOREIGN KEY (blog_post) REFERENCES robertlemke_plugin_blog_domain_model_post (flow3_persistence_identifier)");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post_tags_join ADD CONSTRAINT FK_9084A1FA6EC3989 FOREIGN KEY (blog_tag) REFERENCES robertlemke_plugin_blog_domain_model_tag (flow3_persistence_identifier)");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post_relatedposts_join ADD CONSTRAINT FK_59B5CBA8BA5AE01D FOREIGN KEY (blog_post) REFERENCES robertlemke_plugin_blog_domain_model_post (flow3_persistence_identifier)");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post_relatedposts_join ADD CONSTRAINT FK_59B5CBA84162C001 FOREIGN KEY (related_id) REFERENCES robertlemke_plugin_blog_domain_model_post (flow3_persistence_identifier)");
	}

	/**
	 * @param Schema $schema
	 * @return void
	 */
	public function down(Schema $schema) {
		$this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post_tags_join DROP FOREIGN KEY FK_9084A1FA6EC3989");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post DROP FOREIGN KEY FK_E83ED716C0155143");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post DROP FOREIGN KEY FK_E83ED71664C19C1");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post DROP FOREIGN KEY FK_E83ED716C53D045F");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_comment DROP FOREIGN KEY FK_AE0F05295A8A6C8D");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post_tags_join DROP FOREIGN KEY FK_9084A1FABA5AE01D");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post_relatedposts_join DROP FOREIGN KEY FK_59B5CBA8BA5AE01D");
		$this->addSql("ALTER TABLE robertlemke_plugin_blog_domain_model_post_relatedposts_join DROP FOREIGN KEY FK_59B5CBA84162C001");
		$this->addSql("DROP TABLE robertlemke_plugin_blog_domain_model_tag");
		$this->addSql("DROP TABLE robertlemke_plugin_blog_domain_model_blog");
		$this->addSql("DROP TABLE robertlemke_plugin_blog_domain_model_category");
		$this->addSql("DROP TABLE robertlemke_plugin_blog_domain_model_comment");
		$this->addSql("DROP TABLE robertlemke_plugin_blog_domain_model_image");
		$this->addSql("DROP TABLE robertlemke_plugin_blog_domain_model_post");
		$this->addSql("DROP TABLE robertlemke_plugin_blog_domain_model_post_tags_join");
		$this->addSql("DROP TABLE robertlemke_plugin_blog_domain_model_post_relatedposts_join");
	}
}

?>