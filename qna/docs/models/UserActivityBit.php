<?php

namespace common\models;

class UserActivityBit extends generated\UserActivityBit
{
    const WORK_15M_SIZE = 4; //every 15-minutes interval duration saved as 4 bits
    const ACTIVITY_15M_SIZE = 8; //every 15-minutes interval activity saved as 8 bits

    /**
     * Returns DECIMAL representation of activity placed in $position
     * @param int $position serial number of the 15-minutes interval
     * @return int
     */
    public function getActivityAt($position) {
        return bindec(substr($this->actvs, $position * self::ACTIVITY_15M_SIZE, self::ACTIVITY_15M_SIZE));
    }

    /**
     * Returns DECIMAL representation of duration placed in $position
     * @param int $position serial number of the 15-minutes interval
     * @return int
     */
    public function getDurationAt($position) {
        return bindec(substr($this->works, $position * self::WORK_15M_SIZE, self::WORK_15M_SIZE));
    }

    /**
     * Updating activity and duration data inside the corresponding bitwise strings
     * @param int $position position to update - 15-min index of a needed interval
     * @param int $activity activity to update
     * @param int $duration duration in MINUTES to update
     * @param bool $save whether to save the record after making changes
     * @return bool
     */
    public function putTimeAt($position, $activity, $duration, $save = false) {
        try {
            $activityAt = $this->getActivityAt($position);
            $durationAt = $this->getDurationAt($position);

            $newDuration = $duration;
            $newActivity = $activity;

            //If there was activity in the interval, add duration and recalculate activity
            if ($durationAt != 0) {
                $newDuration = $durationAt + $duration;
                $newActivity = round(($activityAt * $durationAt + $activity * $duration) / $newDuration);
            }

            $this->actvs = substr_replace(
                $this->actvs,
                str_pad(decbin($newActivity), self::ACTIVITY_15M_SIZE, '0', STR_PAD_LEFT),
                $position * self::ACTIVITY_15M_SIZE,
                self::ACTIVITY_15M_SIZE
            );

            $this->works = substr_replace(
                $this->works,
                str_pad(decbin($newDuration), self::WORK_15M_SIZE, '0', STR_PAD_LEFT),
                $position * self::WORK_15M_SIZE,
                self::WORK_15M_SIZE
            );

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
