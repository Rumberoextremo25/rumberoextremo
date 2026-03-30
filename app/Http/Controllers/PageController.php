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
        // 1. Validar los datos del formulario con medidas anti-bot (más permisivo)
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email|max:255',
            'honeypot' => 'nullable|string|max:0', // Campo honeypot oculto
            'timestamp' => 'nullable|integer|min:' . (time() - 10), // Más flexible, 10 segundos
        ], [
            'email.unique' => 'Este correo electrónico ya está suscrito al newsletter.',
            'email.required' => 'Por favor, introduce tu correo electrónico.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'timestamp.min' => 'Por favor, espera unos segundos antes de enviar.',
        ]);

        // 2. Verificar rate limiting por IP (más permisivo, solo por IP)
        $ipAddress = $request->ip();
        $rateLimitKey = "newsletter_subscribe:{$ipAddress}";

        // Verificar si ha habido demasiados intentos (máximo 5 por hora desde la misma IP)
        $attempts = Cache::get($rateLimitKey, 0);
        if ($attempts >= 5) {
            Log::warning('Rate limit excedido para newsletter', [
                'ip' => $ipAddress,
                'email' => $request->email,
                'attempts' => $attempts
            ]);
            return redirect()->back()->with('newsletter_error', 'Has realizado demasiados intentos. Por favor, espera una hora.');
        }

        // 3. Verificar si el email es de dominio temporal o sospechoso
        $suspiciousDomains = [
            'tempmail',
            '10minutemail',
            'guerrillamail',
            'throwaway',
            'mailinator',
            'yopmail',
            'trashmail',
            'fakeinbox',
            'temp-mail',
            'dispostable',
            'guerrillamail',
            'jetable',
            'spamgourmet'
        ];

        $emailDomain = substr(strrchr($request->email, "@"), 1);
        foreach ($suspiciousDomains as $suspiciousDomain) {
            if (stripos($emailDomain, $suspiciousDomain) !== false) {
                Log::info('Intento de suscripción con dominio sospechoso', [
                    'email' => $request->email,
                    'ip' => $ipAddress
                ]);
                // Simulamos éxito pero no guardamos
                return redirect()->back()->with('newsletter_success', '¡Gracias por suscribirte a nuestro newsletter! Revisa tu correo electrónico para la confirmación.');
            }
        }

        // 4. Incrementar contador de intentos SOLO si pasó las validaciones básicas
        Cache::put($rateLimitKey, $attempts + 1, now()->addHour());

        // 5. Guardar con estado pendiente de confirmación
        try {
            $subscriber = NewsletterSubscriber::create([
                'email' => $request->email,
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
                'confirmed_at' => null,
                'confirmation_token' => \Illuminate\Support\Str::random(64),
                'subscribed_at' => now()
            ]);

            // 6. Enviar correo de confirmación en cola
            try {
                // Enviar email en cola para no bloquear la respuesta
                Mail::to($request->email)->send(new NewsletterConfirmationMail(
                    $subscriber->confirmation_token,
                    $subscriber->email
                ));

                // Limpiar rate limit para usuarios exitosos
                Cache::forget($rateLimitKey);

                return redirect()->back()->with('newsletter_success', '¡Gracias por suscribirte! Revisa tu correo para confirmar tu suscripción.');
            } catch (\Exception $e) {
                // Si falla el email, eliminamos el registro
                $subscriber->delete();
                Log::error('Error al enviar correo de confirmación: ' . $e->getMessage());
                return redirect()->back()->with('newsletter_error', 'Error al enviar el correo. Por favor, intenta de nuevo.');
            }
        } catch (\Exception $e) {
            Log::error('Error al guardar suscripción: ' . $e->getMessage());
            return redirect()->back()->with('newsletter_error', 'Error al procesar tu solicitud. Por favor, intenta de nuevo.');
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
