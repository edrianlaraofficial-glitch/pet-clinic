@extends('layouts.app')
@section('title', 'A little extra love for your best friend')

@section('content')
    <section class="hero" id="about" data-scroll-reveal>
        <div class="hero-copy">
            <p class="hero-kicker"><span class="kicker-paw" aria-hidden="true">✳</span> Your neighborhood dog clinic</p>
            <h1>Good care for every <span>good dog.</span></h1>
            <p class="hero-description">From first puppy shots to those just-in-case checkups, we make vet visits feel a little more like belly rubs.</p>
            <div class="hero-actions">
                <a href="{{ auth()->check() ? route('booking.create') : route('login') }}" class="btn btn-primary">
                    Book a visit <span aria-hidden="true">↗</span>
                </a>
                <a href="#services" class="hero-text-link">Meet our services <span aria-hidden="true">↓</span></a>
            </div>
            <a href="#hours" class="clinic-status {{ $clinicIsOpen ? 'clinic-status-open' : '' }}" aria-label="{{ $clinicStatus }}. {{ $clinicStatusDetails }}">
                <span class="clinic-status-dot" aria-hidden="true"></span>
                <span class="clinic-status-copy">
                    <strong>{{ $clinicStatus }}</strong>
                    <small>{{ $clinicStatusDetails }}</small>
                </span>
                <span class="clinic-status-arrow" aria-hidden="true">↗</span>
            </a>
            <div class="hero-note">
                <span class="hero-note-stars" aria-hidden="true">★★★★★</span>
                <span>Kind people. Comfy paws. No ruff days.</span>
            </div>
        </div>

        <div class="hero-art" aria-hidden="true">
            <div class="hero-art-sun"></div>
            <span class="hero-spark hero-spark-one">✳</span>
            <span class="hero-spark hero-spark-two">✦</span>
            <span class="hero-doodle hero-doodle-one"></span>
            <span class="hero-doodle hero-doodle-two"></span>
            <svg class="clinic-dog" viewBox="0 0 440 420" fill="none" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="220" cy="375" rx="119" ry="17" fill="#214B43" fill-opacity=".12"/>
                <g class="puppy-tail">
                    <path d="M286 276c36 2 48-18 56-42 5-15 19-21 30-12 11 10 5 34-8 51-18 23-43 34-77 26l-1-23Z" fill="#D98F3D"/>
                    <path d="M337 237c8-12 20-19 31-15" stroke="#F1B94E" stroke-width="4" stroke-linecap="round"/>
                </g>
                <path d="M144 253c0-31 23-54 53-54h47c30 0 53 23 53 54v76c0 19-15 34-34 34h-16v-54h-52v54h-16c-19 0-35-15-35-34v-76Z" fill="#F1B94E"/>
                <path d="M174 312h31v52h-24c-12 0-22-10-22-22v-8c0-12 6-19 15-22Zm61 0h31c9 3 15 10 15 22v8c0 12-10 22-22 22h-24v-52Z" fill="#FFF0C5"/>
                <path d="M151 109c-26-26-58-21-66 5-10 32 6 78 34 91 21 10 42-13 49-49l-17-47Z" fill="#D98F3D"/>
                <path d="M289 109c26-26 58-21 66 5 10 32-6 78-34 91-21 10-42-13-49-49l17-47Z" fill="#D98F3D"/>
                <path d="M220 55c-59 0-96 43-96 105 0 57 37 96 96 96s96-39 96-96c0-62-37-105-96-105Z" fill="#F1B94E"/>
                <path d="M160 171c0-34 26-58 60-58s60 24 60 58c0 36-26 64-60 64s-60-28-60-64Z" fill="#FFF4D9"/>
                <path d="M175 143c5-9 17-13 27-8" stroke="#8A542D" stroke-width="5" stroke-linecap="round"/>
                <path d="M238 135c10-5 22-1 27 8" stroke="#8A542D" stroke-width="5" stroke-linecap="round"/>
                <ellipse cx="190" cy="157" rx="10" ry="13" fill="#253B35"/>
                <ellipse cx="250" cy="157" rx="10" ry="13" fill="#253B35"/>
                <circle cx="193" cy="153" r="3" fill="#FFF4D9"/>
                <circle cx="253" cy="153" r="3" fill="#FFF4D9"/>
                <ellipse cx="167" cy="183" rx="10" ry="6" fill="#F4B5A0" fill-opacity=".65"/>
                <ellipse cx="273" cy="183" rx="10" ry="6" fill="#F4B5A0" fill-opacity=".65"/>
                <ellipse cx="220" cy="179" rx="14" ry="10" fill="#253B35"/>
                <path d="M220 188v10m0 0c-9 11-21 11-28 3m28-3c9 11 21 11 28 3" stroke="#253B35" stroke-width="4" stroke-linecap="round"/>
                <path d="M211 207c4-5 14-5 18 0 5 7 0 18-9 20-9-2-14-13-9-20Z" fill="#E96F53"/>
                <path d="M145 252c21 17 47 25 75 25s54-8 75-25v16c-21 17-47 25-75 25s-54-8-75-25v-16Z" fill="#E96F53"/>
                <path d="m220 288 20 22-20 26-20-26 20-22Z" fill="#FFF4D9" stroke="#E96F53" stroke-width="5" stroke-linejoin="round"/>
                <path d="M126 75c-8-14-7-27 1-39m186 39c8-14 7-27-1-39" stroke="#4F8B70" stroke-width="7" stroke-linecap="round"/>
                <circle cx="127" cy="35" r="8" fill="#4F8B70"/>
                <circle cx="313" cy="35" r="8" fill="#4F8B70"/>
            </svg>
            <div class="hero-sticker">
                <span class="sticker-paw" aria-hidden="true">🐾</span>
                <span><strong>All paws welcome</strong><small>Big, small, shy & silly</small></span>
            </div>
        </div>
    </section>

    <section class="about-preview" aria-labelledby="about-preview-title" data-scroll-reveal>
        <div class="about-preview-intro">
            <p class="step-label">A little more about us</p>
            <h2 id="about-preview-title">A good place for good dogs.</h2>
        </div>
        <div class="about-preview-copy">
            <p>We’re a neighborhood animal clinic in Trento, here to make every visit feel a little easier for you and your best friend.</p>
            <p class="about-preview-note">More about our clinic and team is coming soon.</p>
            <a href="#hours" class="about-preview-link">See when we’re open <span aria-hidden="true">↓</span></a>
        </div>
    </section>

    <section class="gallery-section" id="gallery" data-scroll-reveal>
        <div class="section-heading">
            <div>
                <p class="step-label">A few familiar faces</p>
                <h2>Life at Wade-County.</h2>
            </div>
            <div class="gallery-heading-aside">
                <p class="section-aside">Little moments from our clinic and its lovely visitors.</p>
                @if ($galleryImages->isNotEmpty())
                    <span class="gallery-photo-count"><span aria-hidden="true">✳</span> {{ str_pad((string) $galleryImages->count(), 2, '0', STR_PAD_LEFT) }} PHOTOS</span>
                @endif
            </div>
        </div>
        @if ($galleryImages->isNotEmpty())
            <div class="gallery-carousel" data-gallery-carousel>
                <div class="gallery-grid" id="clinic-gallery" data-gallery-track role="region" aria-label="Clinic photo gallery" tabindex="0">
                    @foreach ($galleryImages as $galleryImage)
                        <figure class="gallery-card" data-scroll-reveal>
                            <img src="{{ Storage::disk('public')->url($galleryImage->path) }}" alt="{{ $galleryImage->alt_text }}">
                            <span class="gallery-card-index" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <figcaption>
                                <span class="gallery-card-eyebrow">Wade-County moments</span>
                                <strong>{{ $galleryImage->caption ?: $galleryImage->alt_text }}</strong>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
                <div class="gallery-carousel-controls" data-gallery-controls hidden>
                    <div class="gallery-carousel-position">
                        <span class="gallery-carousel-counter" data-gallery-counter aria-live="polite">01 / {{ str_pad((string) $galleryImages->count(), 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="gallery-carousel-progress" role="progressbar" aria-label="Gallery position" aria-valuemin="1" aria-valuemax="{{ $galleryImages->count() }}" aria-valuenow="1">
                            <span data-gallery-progress></span>
                        </span>
                    </div>
                    <button class="gallery-carousel-button" type="button" data-gallery-previous aria-label="Previous gallery photos" aria-controls="clinic-gallery" disabled>
                        <span aria-hidden="true">‹</span>
                    </button>
                    <button class="gallery-carousel-button" type="button" data-gallery-next aria-label="Next gallery photos" aria-controls="clinic-gallery" disabled>
                        <span aria-hidden="true">›</span>
                    </button>
                </div>
            </div>
        @else
            <div class="gallery-placeholder" data-scroll-reveal>
                <span class="gallery-placeholder-icon" aria-hidden="true">🐾</span>
                <p>Photos of our clinic and its lovely visitors will be here soon.</p>
            </div>
        @endif
    </section>

    <section class="services-section" id="services" data-scroll-reveal>
        <div class="section-heading">
            <div>
                <p class="step-label">A little help for every tail</p>
                <h2>Here for the whole doggy day.</h2>
            </div>
            <p class="section-aside">Good health is a team sport. We’ll be on your side, and your pup’s.</p>
        </div>

        <div class="service-grid">
            @forelse ($services as $service)
                <article @class([
                    'service-card',
                    'service-card-featured' => strtolower($service->name) === 'vaccination',
                ])>
                    <div class="service-card-heading">
                        <span class="service-icon" aria-hidden="true">
                            @switch(strtolower($service->name))
                                @case('general checkup')
                                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 2v2m0 0a3 3 0 0 0-3 3v4a3 3 0 0 0 6 0V7a3 3 0 0 0-3-3Zm0 13v3m-5 3h10"/><path d="M5 9v2a7 7 0 0 0 14 0V9"/></svg>
                                    @break
                                @case('vaccination')
                                    <svg viewBox="0 0 24 24" fill="none"><path d="m18 2 4 4M7 13l4 4m-8 4 5-5m-4-4 4 4m-1-2 8-8a2.8 2.8 0 0 1 4 4l-8 8"/></svg>
                                    @break
                                @case('grooming')
                                    <svg viewBox="0 0 24 24" fill="none"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="m8.2 8.2 12.6 12.6m-12.6-5 12.6-12.6"/></svg>
                                    @break
                                @case('dental cleaning')
                                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 3c-1.7-1.4-4.6-1.4-6.3 0C2 6 4.2 10.5 5.1 14.1 6 17.7 7.1 21 9 21c1.6 0 1.3-5 3-5s1.4 5 3 5c1.9 0 3-3.3 3.9-6.9C19.8 10.5 22 6 18.3 3 16.6 1.6 13.7 1.6 12 3Z"/></svg>
                                    @break
                                @case('spay / neuter consultation')
                                    <svg viewBox="0 0 24 24" fill="none"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/><path d="M3 12h5l2-3 3 6 2-3h6"/></svg>
                                    @break
                                @default
                                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 22v-5m0 0c-1.8-2.4-5.2-2.4-7-1-2 1.6-2.6 4.2-1.1 5.4 1.4 1.1 4.2.4 5.3-1.1.8-1.1 1.2-2.2 1.8-3.3Zm0 0c1.8-2.4 5.2-2.4 7-1 2 1.6 2.6 4.2 1.1 5.4-1.4 1.1-4.2.4-5.3-1.1-.8-1.1-1.2-2.2-1.8-3.3ZM5 8c0-2 1.2-3.5 2.7-3.5S10 6 10 8s-1 3-2.5 3S5 10 5 8Zm9 0c0-2 1.2-3.5 2.7-3.5S19 6 19 8s-1 3-2.5 3S14 10 14 8Zm-5-4c0-1.7 1.3-3 3-3s3 1.3 3 3-1.3 3-3 3-3-1.3-3-3Z"/></svg>
                            @endswitch
                        </span>
                        <span class="service-card-label">Veterinary care</span>
                    </div>
                    <h3>{{ $service->name }}</h3>
                    <p>{{ $service->description }}</p>
                    <div class="service-meta">
                        <span class="service-duration">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                            {{ $service->duration_minutes }} min
                        </span>
                        <strong>₱{{ number_format($service->price, 0) }}</strong>
                    </div>
                    <a class="service-book-link" href="{{ auth()->check() ? route('booking.create') : route('login') }}">Book this visit</a>
                </article>
            @empty
                <p class="muted">Services are being updated. Please check back soon.</p>
            @endforelse
        </div>

        @if ($services->isNotEmpty())
            <p class="service-price-note">Prices are estimates. Final cost depends on your dog’s needs.</p>
        @endif
    </section>

    <section class="ratings-section" id="ratings" aria-labelledby="ratings-title" data-scroll-reveal>
        <div class="ratings-panel">
            <div class="ratings-copy">
                <p class="step-label">Kind words, happy tails</p>
                <h2 id="ratings-title">Kind care means the world.</h2>
                <p>We’re grateful to the pet parents who share their experiences and help other families feel at ease before a visit.</p>
                <a class="ratings-google-link" href="https://www.google.com/maps/search/?api=1&amp;query=Wade-County+Animal+Clinic%2C+Garcia+St.%2C+P-6%2C+Poblacion%2C+Trento%2C+8505+Agusan+del+Sur%2C+Philippines" target="_blank" rel="noopener noreferrer">
                    Read our reviews on Google <span aria-hidden="true">↗</span>
                </a>
            </div>

            <aside class="ratings-score-card" aria-label="5.0 out of 5 based on 2 Google reviews">
                <span class="ratings-google"><span class="google-g" aria-hidden="true">G</span> Google reviews</span>
                <div class="ratings-score-line">
                    <strong class="ratings-score">5.0</strong>
                    <span class="ratings-score-caption">out of 5</span>
                </div>
                <span class="ratings-stars" aria-label="5 out of 5 stars">★★★★★</span>
                <p class="ratings-count">Based on 2 reviews</p>
            </aside>
        </div>
    </section>

    <section class="visit-section" id="hours" data-scroll-reveal>
        <div class="visit-card" data-scroll-reveal>
            <div class="visit-heading">
                <span class="visit-icon" aria-hidden="true">◷</span>
                <div>
                    <p class="step-label">Come on by</p>
                    <h2>Our opening hours</h2>
                </div>
            </div>
            <table class="hours-table">
                <tbody>
                    @foreach ($hours as $hour)
                        <tr @class(['hours-today' => $hour->weekday === $todayWeekday])>
                            <td>
                                <strong>{{ $hour->dayName() }}</strong>
                                @if ($hour->weekday === $todayWeekday)
                                    <span class="hours-today-label">Today</span>
                                @endif
                            </td>
                            <td class="{{ $hour->is_closed ? 'muted' : 'hours-open' }}">
                                @if ($hour->is_closed)
                                    Closed for snoozes
                                @else
                                    {{ \Carbon\Carbon::parse($hour->opens_at)->format('g:i A') }} – {{ \Carbon\Carbon::parse($hour->closes_at)->format('g:i A') }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="steps-card" data-scroll-reveal>
            <p class="step-label">Easy as one, two, woof</p>
            <h2>Your next visit, sorted.</h2>
            <ol class="steps-list">
                <li><span>01</span><div><strong>Make an account</strong><small>Confirm your email and you’re in.</small></div></li>
                <li><span>02</span><div><strong>Add your bestie</strong><small>Tell us a little about your dog.</small></div></li>
                <li><span>03</span><div><strong>Pick a time</strong><small>Choose a service and a slot that works.</small></div></li>
            </ol>
            <a href="{{ auth()->check() ? route('booking.create') : route('login') }}" class="btn btn-dark">Let’s get started <span aria-hidden="true">↗</span></a>
        </div>
    </section>
@endsection
