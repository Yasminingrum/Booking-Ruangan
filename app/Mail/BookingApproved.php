<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class BookingApproved extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking->load(['user', 'room', 'approver']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Peminjaman Ruangan Disetujui',
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
        $approvedAt = $this->booking->approved_at
            ? ($this->booking->approved_at instanceof Carbon
                ? $this->booking->approved_at
                : Carbon::parse($this->booking->approved_at))
            : null;

        return new Content(
            view: 'emails.booking-approved',
            with: [
                'userName' => $this->booking->user->name,
                'roomName' => optional($this->booking->room)->name,
                'roomLocation' => optional($this->booking->room)->location,
                'roomFacilities' => optional($this->booking->room)->facilities,
                'bookingDate' => $bookingDate->format('d F Y'),
                'dayName' => $bookingDate->translatedFormat('l'),
                'startTime' => $startTime->format('H:i'),
                'endTime' => $endTime->format('H:i'),
                'purpose' => $this->booking->purpose,
                'participants' => $this->booking->participants,
                'approvedBy' => optional($this->booking->approver)->name ?? 'Admin',
                'approvedAt' => $approvedAt ? $approvedAt->format('d F Y H:i') : null,
                'bookingId' => $this->booking->id,
                'detailUrl' => url("/bookings/{$this->booking->id}"),
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
