<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Services\BookingService;
use App\Services\ConflictValidationService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class BookingServiceConflictTest extends TestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    public function test_new_booking_is_blocked_when_conflicting_approved_booking_exists(): void
    {
        $room = Room::factory()->create(['capacity' => 20]);
        $admin = User::factory()->admin()->create();
        $approvedUser = User::factory()->peminjam()->create();
        $newUser = User::factory()->peminjam()->create();

        Booking::factory()
            ->for($room)
            ->for($approvedUser)
            ->state([
                'booking_date' => '2025-10-20',
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'status' => Booking::STATUS_APPROVED,
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ])
            ->create();

        $existing = Booking::first();
        $this->assertNotNull($existing);
        $this->assertSame($room->id, $existing->room_id);
        $this->assertSame('2025-10-20', $existing->booking_date->format('Y-m-d'));
        $this->assertSame('10:00:00', $existing->start_time);
        $this->assertSame('12:00:00', $existing->end_time);
        $this->assertSame(Booking::STATUS_APPROVED, $existing->status);

        $notificationService = Mockery::mock(NotificationService::class);
        $notificationService->shouldReceive('sendBookingCreatedNotification')->never();

        $bookingService = new BookingService(
            new ConflictValidationService(),
            $notificationService
        );

        $this->assertTrue((new ConflictValidationService())->hasTimeOverlap(
            '10:00:00',
            '12:00:00',
            '10:00:00',
            '12:00:00'
        ));

        $conflict = (new ConflictValidationService())->findFirstConflict(
            $room->id,
            '2025-10-20',
            '10:00:00',
            '12:00:00'
        );

        $this->assertNotNull($conflict);
        $this->assertSame(Booking::STATUS_APPROVED, $conflict->status);

        try {
            $bookingService->createBooking([
                'user_id' => $newUser->id,
                'room_id' => $room->id,
                'booking_date' => '2025-10-20',
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'purpose' => 'Kegiatan Test',
                'participants' => 10,
                'is_recurring' => false,
                'recurring_pattern' => null,
            ]);

            $this->fail('Booking creation should fail when an approved booking already exists for the same slot.');
        } catch (\Exception $e) {
            $this->assertSame(
                'Pengajuan tidak dapat dilanjutkan karena ruangan sudah disetujui pada waktu tersebut. Silakan pilih waktu lainnya.',
                $e->getMessage()
            );
        }
    }
}
