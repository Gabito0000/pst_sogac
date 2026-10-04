{{--
    Formulario compartido por el alta y la edición de una pregunta frecuente.
    Antes el alta y la edición eran modales distintos dentro de la misma vista
    de lectura, cada uno con su propio marcado.

    Renderiza el <form> completo porque @csrf y @method solo funcionan dentro
    de la etiqueta.

    Espera:
        $accion   ruta destino del formulario
        $metodo   'POST' para crear, 'PUT' para actualizar
        $pregunta modelo existente, o null en el alta
--}}
<form action="{{ $accion }}" method="POST" class="form">
    @csrf
    @if ($metodo !== 'POST')
        @method($metodo)
    @endif

    <div class="field">
        <label for="pregunta">
            Pregunta <span class="req">*</span>
        </label>

        <input type="text"
               name="pregunta"
               id="pregunta"
               maxlength="200"
               required
               placeholder="Ej: ¿Cuánto tarda en resolverse una constancia de estudio?"
               value="{{ old('pregunta', $pregunta->pregunta ?? '') }}"
               @class(['field--invalid' => $errors->has('pregunta')])>

        @error('pregunta')
            <span class="field__error">{{ $message }}</span>
        @enderror

        <span class="field__hint">Es el texto que el estudiante lee en la lista. Máximo 200 caracteres.</span>
    </div>

    <div class="field">
        <label for="respuesta">
            Respuesta <span class="req">*</span>
        </label>

        <textarea name="respuesta"
                  id="respuesta"
                  rows="7"
                  maxlength="4000"
                  required
                  placeholder="Explica el procedimiento, los requisitos y los plazos. Se puede escribir en varios párrafos."
                  @class(['field--invalid' => $errors->has('respuesta')])>{{ old('respuesta', $pregunta->respuesta ?? '') }}</textarea>

        @error('respuesta')
            <span class="field__error">{{ $message }}</span>
        @enderror

        <span class="field__hint">Los saltos de línea se conservan al mostrarlo. Máximo 4000 caracteres.</span>
    </div>

    <div class="actions">
        <button type="submit" class="btn btn--primary">
            {{ $pregunta ? 'Guardar cambios' : 'Crear pregunta' }}
        </button>

        <a href="{{ route('admin.preguntas.index') }}" class="btn btn--ghost">Cancelar</a>
    </div>

    <p>
        <small style="color: var(--gray-400);">
            Comprueba cómo lo verá el estudiante en
            <a href="{{ route('user.ayuda.preguntas') }}">Preguntas frecuentes</a>.
        </small>
    </p>
</form>
