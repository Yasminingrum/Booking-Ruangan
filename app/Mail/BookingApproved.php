<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingApproved extends Mailable
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
        return new Content(
            view: 'emails.booking-approved',
            with: [
                'userName' => $this->booking->user->name,
                'roomName' => $this->booking->room->name,
                'roomLocation' => $this->booking->room->location,
                'roomFacilities' => $this->booking->room->facilities,
                'bookingDate' => $this->booking->booking_date->format('d F Y'),
                'dayName' => $this->booking->booking_date->translatedFormat('l'),
                'startTime' => $this->booking->start_time->format('H:i'),
                'endTime' => $this->booking->end_time->format('H:i'),
                'purpose' => $this->booking->purpose,
                'participants' => $this->booking->participants,
                'approvedBy' => $this->booking->approver->name ?? 'Admin',
                'approvedAt' => $this->booking->approved_at->format('d F Y H:i'),
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
