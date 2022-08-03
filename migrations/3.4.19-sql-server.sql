ALTER TABLE [ScheduledCommand]
ADD [RetryOnFail] [BIT] NOT NULL DEFAULT (0),
    [RetryDelay] [INT] NOT NULL DEFAULT (60),
    [RetryMaxAttempts] [INT] NOT NULL DEFAULT (60),
    [RetryCount] [INT] NOT NULL DEFAULT (0);
