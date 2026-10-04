<?php

namespace Database\Seeders;

use App\Models\PreguntasFrecuentes;
use Illuminate\Database\Seeder;

/**
 * Contenido de ejemplo para el portal de ayuda del estudiante.
 *
 * Es idempotente: solo crea las preguntas que faltan, de forma que volver a
 * ejecutarlo no duplica el listado ni pisa lo que el administrador haya escrito
 * a mano.
 */
class PreguntasFrecuentesDemoSeeder extends Seeder
{
    /**
     * @var list<array{pregunta: string, respuesta: string}>
     */
    private const PREGUNTAS = [
        [
            'pregunta' => '¿Cuánto tarda en resolverse mi solicitud?',
            'respuesta' => 'El plazo depende del tipo de trámite. El tiempo estimado de cada uno aparece '
                .'publicado en su ficha, dentro del catálogo de Trámites.\n\n'
                .'Si la solicitud lleva más tiempo que ese plazo sin respuesta, escríbenos por el chat y la revisamos.',
        ],
        [
            'pregunta' => '¿Puedo cancelar una solicitud que ya envié?',
            'respuesta' => 'Solo mientras siga en estado Pendiente. En cuanto un administrador la apruebe '
                .'o la rechace ya no se puede cancelar.',
        ],
        [
            'pregunta' => '¿Qué documentos necesito para el trámite?',
            'respuesta' => 'Cada tipo de trámite tiene su propia lista de requisitos. La encontrarás en la '
                .'ficha del trámite, en el apartado Requisitos, antes de enviar la solicitud.',
        ],
        [
            'pregunta' => 'Me equivoqué al escribir un dato. ¿Puedo editarlo?',
            'respuesta' => 'No. La información queda congelada al enviar la solicitud para poder auditar el '
                .'trámite.\n\nSi el dato es incorrecto, abre un chat con soporte y te ayudamos a corregirlo.',
        ],
        [
            'pregunta' => '¿La declaración jurada tiene fecha de vencimiento?',
            'respuesta' => 'Sí. Debe tener menos de tres meses de expedida en el momento en que el '
                .'administrador revise tu solicitud.',
        ],
        [
            'pregunta' => '¿Puedo pedir la constancia de estudio de otro periodo?',
            'respuesta' => 'Sí. En el formulario de solicitud puedes indicar el periodo académico que '
                .'necesitas. Si fueron varios, abre un chat para que lo coordines con la oficina.',
        ],
        [
            'pregunta' => '¿Cómo sé que el administrador ya vio mi solicitud?',
            'respuesta' => 'El estado de tu solicitud se actualiza en Mis solicitudes. Si sigue en Pendiente '
                .'por más de un día hábil, te recomendamos escribir por el chat.',
        ],
    ];

    public function run(): void
    {
        foreach (self::PREGUNTAS as $pregunta) {
            PreguntasFrecuentes::firstOrCreate(
                ['pregunta' => $pregunta['pregunta']],
                ['respuesta' => $pregunta['respuesta']],
            );
        }

        $this->command?->info('Preguntas frecuentes de ejemplo: '.PreguntasFrecuentes::count());
    }
}
