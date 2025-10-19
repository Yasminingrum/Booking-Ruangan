<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class BookingCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $booking;
    public $admin;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, User $admin)
    {
        $this->booking = $booking->load(['user', 'room']);
        $this->admin = $admin;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengajuan Peminjaman Ruangan Baru',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $bookingDate = $this->booking->booking_date instanceof Carbon
            ? $this->booking->booking_date
            : Carbon::parse($this->booking->booking_date);

        $startTime = Carbon::parse($this->booking->start_time);
        $endTime = Carbon::parse($this->booking->end_time);

        return new Content(
            view: 'emails.booking-created',
            with: [
                'adminName' => $this->admin->name,
                'peminjamName' => $this->booking->user->name,
                'peminjamEmail' => $this->booking->user->email,
                'peminjamPhone' => $this->booking->user->phone,
                'roomName' => optional($this->booking->room)->name,
                'roomLocation' => optional($this->booking->room)->location,
                'bookingDate' => $bookingDate->format('d F Y'),
                'startTime' => $startTime->format('H:i'),
                'endTime' => $endTime->format('H:i'),
                'purpose' => $this->booking->purpose,
                'participants' => $this->booking->participants,
                'bookingId' => $this->booking->id,
                'approveUrl' => url("/admin/bookings/{$this->booking->id}/approve"),
                'detailUrl' => url("/admin/bookings/{$this->booking->id}"),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
