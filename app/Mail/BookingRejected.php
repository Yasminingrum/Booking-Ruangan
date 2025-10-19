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

class BookingRejected extends Mailable implements ShouldQueue
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
        $bookingDate = $this->booking->booking_date instanceof Carbon
            ? $this->booking->booking_date
            : Carbon::parse($this->booking->booking_date);

        $startTime = Carbon::parse($this->booking->start_time);
        $endTime = Carbon::parse($this->booking->end_time);

        return new Content(
            view: 'emails.booking-rejected',
            with: [
                'userName' => $this->booking->user->name,
                'roomName' => optional($this->booking->room)->name,
                'bookingDate' => $bookingDate->format('d F Y'),
                'dayName' => $bookingDate->translatedFormat('l'),
                'startTime' => $startTime->format('H:i'),
                'endTime' => $endTime->format('H:i'),
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
