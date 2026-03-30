<?php

namespace App\Http\Controllers;

use App\Mail\ContactConfirmationMail;
use App\Mail\NewsletterConfirmationMail;
use App\Models\AffiliateApplication;
use App\Models\AllyContact;
use App\Models\ContactMessage;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Str;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function about()
    {
        return view('about');
    }

    public function demo()
    {
        return view('demo');
    }

    public function showContactForm()
    {
        return view('contact');
    }

    public function storeContactMessage(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message_content' => 'required|string',
        ], [
            'message_content.required' => 'El campo de mensaje no puede estar vacío.',
        ]);

        ContactMessage::create($validatedData);

        try {
            Mail::to($validatedData['email'])->send(new ContactConfirmationMail($validatedData));
            // Opcional: Si también quieres que un correo llegue a ti/administrador
            // Mail::to('tu_correo_admin@ejemplo.com')->send(new AdminNotificationMail($validatedData));
        } catch (\Exception $e) {
            // Log the error, but don't prevent the user from seeing a success message
            Log::error('Error sending contact confirmation email: ' . $e->getMessage());
        }

        return redirect()->route('welcome')->with('success', '¡Tu mensaje ha sido recibido con éxito! Te responderemos a la brevedad posible.');
    }

    public function aliado()
    {
        return view('demo.aliado');
    }

    public function storeAllyContact(Request $request)
    {
        // 1. Validar los datos del formulario
        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:ally_contacts', // Asegura que el email sea único para contactos de aliados
            'phone' => 'nullable|string|max:20',
            'partnership_type' => 'required|string|in:venue_partnership,event_promotion,brand_collaboration,media_partnership,other',
            'website' => 'nullable|url|max:255',
            'message' => 'required|string',
        ], [
            'email.unique' => 'Ya existe un contacto de aliado con este correo electrónico.',
            'message.required' => 'Por favor, describe tu propuesta de colaboración.',
        ]);

        // 2. Crear una nueva instancia del modelo y guardar los datos
        AllyContact::create($validatedData);

        // 3. Redirigir al usuario a la vista home con un mensaje de éxito
        return redirect()->route('welcome')->with('success', '¡Tu propuesta de colaboración ha sido enviada con éxito! Nos pondremos en contacto contigo pronto.');
    }

    public function afiliado()
    {
        return view('demo.afiliado');
    }

    public function storeAffiliateApplication(Request $request)
    {
        // 1. Validar los datos del formulario
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:affiliate_applications',
            'phone' => 'nullable|string|max:20',
            'social_media_link' => 'required|url|max:255',
            'followers_count' => 'required|integer|min:0',
            'affiliate_type' => 'required|string|in:influencer,promoter,venue_owner,other',
            'message' => 'nullable|string',
            'terms' => 'required|accepted', // Asegura que el checkbox sea marcado
        ], [
            'email.unique' => 'Ya existe una solicitud de afiliado con este correo electrónico.',
            'terms.accepted' => 'Debes aceptar los Términos y Condiciones para enviar tu solicitud.',
        ]);

        // 2. Crear una nueva instancia del modelo y guardar los datos
        AffiliateApplication::create($validatedData);

        // 3. Redirigir al usuario con un mensaje de éxito
        return redirect()->route('welcome')->with('success', '¡Tu solicitud de afiliado ha sido enviada con éxito! Nos pondremos en contacto contigo pronto.');
    }

    // Nuevo método para manejar la suscripción al newsletter
    public function subscribeToNewsletter(Request $request)
    {
        // 1. Validar los datos del formulario con medidas anti-bot
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email|max:255',
            'honeypot' => 'sometimes|string|max:0', // Campo honeypot oculto
            'timestamp' => 'required|integer|min:' . (time() - 5), // Prevenir envíos muy rápidos
        ], [
            'email.unique' => 'Este correo electrónico ya está suscrito al newsletter.',
            'email.required' => 'Por favor, introduce tu correo electrónico.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'timestamp.min' => 'Solicitud inválida. Por favor, espera unos segundos.',
        ]);

        // 2. Verificar rate limiting por IP y email
        $ipAddress = $request->ip();
        $email = $request->email;
        $rateLimitKey = "newsletter_subscribe:{$ipAddress}:{$email}";

        // Verificar si ha habido demasiados intentos (máximo 3 por hora)
        $attempts = Cache::get($rateLimitKey, 0);
        if ($attempts >= 3) {
            Log::warning('Rate limit excedido para newsletter', [
                'ip' => $ipAddress,
                'email' => $email,
                'attempts' => $attempts
            ]);
            return redirect()->back()->with('newsletter_error', 'Has realizado demasiados intentos. Por favor, espera una hora.');
        }

        // Incrementar contador de intentos
        Cache::put($rateLimitKey, $attempts + 1, now()->addHour());

        // 3. Verificar si el email es de dominio temporal o sospechoso
        $suspiciousDomains = [
            'tempmail',
            '10minutemail',
            'guerrillamail',
            'throwaway',
            'mailinator',
            'yopmail',
            'trashmail',
            'fakeinbox'
        ];

        $emailDomain = substr(strrchr($email, "@"), 1);
        foreach ($suspiciousDomains as $suspiciousDomain) {
            if (stripos($emailDomain, $suspiciousDomain) !== false) {
                Log::info('Intento de suscripción con dominio sospechoso', [
                    'email' => $email,
                    'ip' => $ipAddress
                ]);
                // Simulamos éxito pero no guardamos para no dar pistas al bot
                return redirect()->back()->with('newsletter_success', '¡Gracias por suscribirte a nuestro newsletter! Revisa tu correo electrónico para la confirmación.');
            }
        }

        // 4. Verificar con servicios de validación de email (opcional)
        // Descomenta si tienes acceso a un servicio como Hunter, NeverBounce, etc.
        /*
    if (!$this->validateEmailWithService($email)) {
        Log::warning('Email no válido según servicio externo', [
            'email' => $email,
            'ip' => $ipAddress
        ]);
        return redirect()->back()->with('newsletter_error', 'El email proporcionado no parece válido.');
    }
    */

        // 5. Verificar token CSRF (Laravel lo hace automáticamente, pero asegúrate que el formulario tenga @csrf)

        // 6. Guardar con transacción y estado pendiente de confirmación
        try {
            $subscriber = NewsletterSubscriber::create([
                'email' => $request->email,
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
                'confirmed_at' => null, // Requerir confirmación por email
                'confirmation_token' => \Illuminate\Support\Str::random(64),
                'subscribed_at' => now()
            ]);

            // 7. Enviar correo de confirmación con token
            try {
                Mail::to($request->email)->send(new NewsletterConfirmationMail($subscriber->confirmation_token));

                // Limpiar el rate limit solo si el email se envió correctamente
                Cache::forget($rateLimitKey);

                return redirect()->back()->with('newsletter_success', '¡Gracias por suscribirte! Por favor, revisa tu correo electrónico para confirmar tu suscripción.');
            } catch (\Exception $e) {
                // Si falla el email, eliminamos el registro pendiente
                $subscriber->delete();
                Log::error('Error al enviar correo de confirmación del newsletter: ' . $e->getMessage());
                return redirect()->back()->with('newsletter_error', 'Hubo un problema técnico. Por favor, intenta de nuevo más tarde.');
            }
        } catch (\Exception $e) {
            Log::error('Error al guardar suscripción al newsletter: ' . $e->getMessage());
            return redirect()->back()->with('newsletter_error', 'No pudimos procesar tu solicitud. Por favor, intenta de nuevo.');
        }
    }

    // Método auxiliar para validar email con servicios externos
    private function validateEmailWithService($email)
    {
        // Implementación de ejemplo para usar con algún servicio
        // Retorna true si el email es válido, false si no
        return true;
    }

    public function terms()
    {
        return view('terms');
    }

    public function privacy()
    {
        return view('privacy');
    }

    public function faqs()
    {
        return view('faq');
    }
}
