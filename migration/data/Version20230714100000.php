<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidSolutionCatalysts\EasyCredit\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Psr\Log\LoggerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230714100000 extends AbstractMigration
{
    /** @throws Exception */
    public function __construct(Connection $connection, LoggerInterface $logger)
    {
        parent::__construct($connection, $logger);

        $this->platform->registerDoctrineTypeMapping('enum', 'string');
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $oxorder = $schema->getTable("oxorder");
        if (!$oxorder->hasColumn("ECREDINTERESTSVALUE"))
        {
            $oxorder->addColumn("ECREDINTERESTSVALUE",
                Types::FLOAT,
                ["default" => 0, "comment" => "easycredit interests value"]
            );
        }
        if (!$oxorder->hasColumn("ECREDTECHNICALID"))
        {
            $oxorder->addColumn("ECREDTECHNICALID",
                Types::STRING,
                ["default" => "", "comment" => "easycredit technical processid"]
            );
        }
        if (!$oxorder->hasColumn("ECREDFUNCTIONALID"))
        {
            $oxorder->addColumn("ECREDFUNCTIONALID",
                Types::STRING,
                ["default" => "", "comment" => "easycredit functional processid"]
            );
        }
        if (!$oxorder->hasColumn("ECREDPAYMENTSTATUS"))
        {
            $oxorder->addColumn("ECREDPAYMENTSTATUS",
                Types::STRING,
                ["default" => "", "comment" => "easycredit payment status was captured"]
            );
        }
        if (!$oxorder->hasColumn("ECREDCONFIRMRESPONSE"))
        {
            $oxorder->addColumn("ECREDCONFIRMRESPONSE",
                Types::TEXT,
                ["default" => "", "comment" => "easycredit instalment confirmation response"]
            );
        }
        if (!$oxorder->hasColumn("ECREDDELIVERYSTATE"))
        {
            $oxorder->addColumn("ECREDDELIVERYSTATE",
                Types::TEXT,
                ["default" => "", "comment" => "status of order - delivery reported, in account ..."]
            );
        }
    }

    public function down(Schema $schema): void
    {
        $oxorder = $schema->getTable("oxorder");
        $oxorder->dropColumn("ECREDINTERESTSVALUE");
        $oxorder->dropColumn("ECREDTECHNICALID");
        $oxorder->dropColumn("ECREDFUNCTIONALID");
        $oxorder->dropColumn("ECREDPAYMENTSTATUS");
        $oxorder->dropColumn("ECREDCONFIRMRESPONSE");
        $oxorder->dropColumn("ECREDDELIVERYSTATE");
    }
}