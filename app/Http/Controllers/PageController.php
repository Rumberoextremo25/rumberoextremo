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
        // 1. Validar los datos del formulario
        $request->validate([
            'email' => 'required|email|unique:newsletter_subscribers,email|max:255',
        ], [
            'email.unique' => 'Este correo electrónico ya está suscrito al newsletter.',
            'email.required' => 'Por favor, introduce tu correo electrónico.',
            'email.email' => 'El formato del correo electrónico no es válido.',
        ]);

        // 2. Guardar el correo en la base de datos
        NewsletterSubscriber::create(['email' => $request->email]);

        // 3. Enviar el correo de confirmación al usuario
        try {
            Mail::to($request->email)->send(new NewsletterConfirmationMail($request->email));
        } catch (\Exception $e) {
            // Registra el error, pero no impidas que el usuario vea el mensaje de éxito en la web
            Log::error('Error al enviar correo de confirmación del newsletter: ' . $e->getMessage());
            // Opcional: podrías añadir un mensaje flash diferente si el correo falló, aunque la suscripción sí se realizó.
            // return redirect()->back()->with('newsletter_warning', '¡Gracias por suscribirte! Hubo un problema al enviar el correo de confirmación.');
        }

        // 4. Redirigir de vuelta a la página actual con un mensaje de éxito
        return redirect()->back()->with('newsletter_success', '¡Gracias por suscribirte a nuestro newsletter! Revisa tu correo electrónico para la confirmación.');
    }

    public function terms ()
    {
        return view ('terms');
    }

    public function privacy ()
    {
        return view ('privacy');
    }

    public function faqs()
    {
        return view('faq');
    }
}
