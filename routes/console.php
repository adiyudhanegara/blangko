<?php
use Illuminate\Support\Facades\Schedule;

Schedule::command('releases:transition')->hourly();
Schedule::command('releases:reminders')->hourly();
