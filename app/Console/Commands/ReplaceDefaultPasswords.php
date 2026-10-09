<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\AccountInvite;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class ReplaceDefaultPasswords extends Command
{
    protected $signature = 'users:replace-default-passwords {--apply : Replace the passwords and email set-password links}';

    protected $description = 'Find accounts still using the old ballet+DOB (ddmmyyyy) password; with --apply, replace them';

    public function handle(): int
    {
        $this->line('Only the ballet+ddmmyyyy pattern is checked; other passwords are not touched.');

        $matches = collect();
        $skipped = collect();

        foreach (User::whereNotNull('dob')->whereNotNull('password')->get() as $user) {
            if (trim((string) $user->email) === '' || (string) $user->password === '') {
                continue;
            }

            try {
                $raw = (string) $user->getRawOriginal('dob');
                // MySQL without strict mode turns junk into 0000-00-00, which Carbon would accept.
                if (str_starts_with($raw, '0000')) {
                    throw new \InvalidArgumentException('zero date');
                }
                $pattern = 'ballet'.Carbon::parse($raw)->format('dmY');
            } catch (\Throwable) {
                $skipped->push($user);

                continue;
            }

            if (Hash::check($pattern, (string) $user->password)) {
                $matches->push($user);
            }
        }

        foreach ($matches as $user) {
            $this->line("{$user->email} ({$user->role})");
        }
        foreach ($skipped as $user) {
            $this->line("skipped (unreadable dob): {$user->email}");
        }
        $this->info($matches->count().' account(s) use the default password.');

        if (! $this->option('apply')) {
            $this->warn('Dry run: nothing changed. Run again with --apply to replace them.');

            return self::SUCCESS;
        }

        $done = 0;
        $failed = [];
        foreach ($matches as $user) {
            try {
                AccountInvite::send($user);
                $done++;
            } catch (\Throwable $e) {
                $failed[$user->email] = $e->getMessage();
            }
        }
        $this->info("Replaced and emailed {$done} account(s).");

        if ($failed) {
            foreach ($failed as $email => $message) {
                $this->error("{$email}: {$message}");
            }
            $this->warn('These accounts have a random password; send them a link with Forgot password.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
