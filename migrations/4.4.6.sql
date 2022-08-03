ALTER TABLE ScheduledCommand
ADD COLUMN `RunAt` datetime DEFAULT NULL AFTER CronExpression,
ADD COLUMN `Data` longtext COLLATE utf8mb4_unicode_ci AFTER Arguments,
ADD COLUMN `ClearData` bit(1) DEFAULT b'1' AFTER Data,
ADD COLUMN `OnSuccessCommandID` bigint DEFAULT NULL AFTER RunImmediately,
ADD COLUMN `OnFailureCommandID` bigint DEFAULT NULL AFTER OnSuccessCommandID,
ADD COLUMN `OriginalCommandID` bigint DEFAULT NULL AFTER OnFailureCommandID;
