ALTER TABLE ScheduledCommand
ADD COLUMN `RetryOnFail` bit(1) NOT NULL DEFAULT b'0' AFTER ClearData,
ADD COLUMN `RetryDelay` bigint NOT NULL DEFAULT 60 AFTER RetryOnFail,
ADD COLUMN `RetryMaxAttempts` bigint NOT NULL DEFAULT 60 AFTER RetryOnFail,
ADD COLUMN `RetryCount` bigint NOT NULL DEFAULT 0 AFTER RetryMaxAttempts;
