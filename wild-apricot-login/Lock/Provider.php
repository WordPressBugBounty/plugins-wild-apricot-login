<?php

class WA_Lock_Provider
{
    const START_ID = 'start';
    const END_ID = 'end';
    const MIN_TIME = 1427447885;
    
    public static function acquire($lockName, $lockTime = 0)
    {
        $currentTime = time();
        
        set_transient
        (
            $lockName,
            array
            (
                self::START_ID => $currentTime,
                self::END_ID => $currentTime + $lockTime
            )
            ,
            $lockTime
        );
    }
    
    public static function isLocked($lockName)
    {
        $lock = get_transient($lockName);

        return is_array($lock) && is_int($lock[self::START_ID]) && is_int($lock[self::END_ID]) && $lock[self::START_ID] > self::MIN_TIME;
    }
    
    public static function wait($lockName, $maxDelay = 0, $callback = null)
    {
        $lock = get_transient($lockName);
        
        if (!is_array($lock) || !is_int($lock[self::START_ID]) || !is_int($lock[self::END_ID]) || $lock[self::START_ID] < self::MIN_TIME)
        {
            return;
        }
        
        $currentTime = time();
        $delay = ($maxDelay > 0 && ($lock[self::START_ID] >= $lock[self::END_ID] || $lock[self::END_ID] > $currentTime + $maxDelay))
            ? $maxDelay
            : $lock[self::END_ID] - $currentTime;

        if ($delay > 0)
        {
            sleep($delay);

            if (is_callable($callback))
            {
                call_user_func($callback);
            }
        }
    }

    public static function release($lockName)
    {
        if (self::isLocked($lockName))
        {
            delete_transient($lockName);
        }
    }
}