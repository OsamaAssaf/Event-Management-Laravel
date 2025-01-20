<?php

namespace App\Console\Commands;

use App\Models\Attendee;
use App\Models\Event;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sends notifications to all events attendees that event starts soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = Event::with('attendees.user')
            ->whereBetween('start_time', [now(), now()->addDay()])
            ->get();
        $eventCount = count($events);
        $eventLabel = Str::plural('event', $eventCount);
        $this->info("Found {$eventCount} {$eventLabel}");

        $events->each(
            fn(Event $event)  => $event->attendees->each(
                function(Attendee $attendee)use ($event) {
                    $attendee->user->notify(
                        new EventReminderNotification($event),
                    );
                },
            ),
        );
        $this->info('Reminder notification sent successfully!');
    }
}
