<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking->load(['user', 'room']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Peminjaman Ruangan Ditolak',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-rejected',
            with: [
                'userName' => $this->booking->user->name,
                'roomName' => $this->booking->room->name,
                'bookingDate' => $this->booking->booking_date->format('d F Y'),
                'dayName' => $this->booking->booking_date->translatedFormat('l'),
                'startTime' => $this->booking->start_time->format('H:i'),
                'endTime' => $this->booking->end_time->format('H:i'),
                'purpose' => $this->booking->purpose,
                'rejectionReason' => $this->booking->rejection_reason ?? 'Tidak disebutkan',
                'bookingId' => $this->booking->id,
                'searchUrl' => url('/rooms/search'),
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
