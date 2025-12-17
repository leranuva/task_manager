<template>
    <AppLayout title="Perfil">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-900 dark:text-white leading-tight">
                Perfil
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Mensajes de éxito/error -->
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-2"
                >
                    <Alert
                        v-if="showFlashMessage && page.props.flash.message"
                        type="success"
                        dismissible
                        @dismiss="showFlashMessage = false"
                    >
                        {{ page.props.flash.message }}
                    </Alert>
                </Transition>
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-2"
                >
                    <Alert
                        v-if="showFlashError && page.props.flash.error"
                        type="error"
                        dismissible
                        @dismiss="showFlashError = false"
                    >
                        {{ page.props.flash.error }}
                    </Alert>
                </Transition>

                <!-- Actualizar Información del Perfil -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section>
                            <header>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                                    Información del Perfil
                                </h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Actualiza la información de tu cuenta y dirección de correo electrónico.
                                </p>
                            </header>

                            <form @submit.prevent="submitProfileInformation" class="mt-6 space-y-6">
                                <div>
                                    <InputLabel for="name" value="Nombre" />
                                    <TextInput
                                        id="name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="profileForm.name"
                                        :error="profileForm.errors.name"
                                        required
                                        autofocus
                                        autocomplete="name"
                                    />
                                    <InputError class="mt-2" :message="profileForm.errors.name" />
                                </div>

                                <div>
                                    <InputLabel for="email" value="Email" />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        v-model="profileForm.email"
                                        :error="profileForm.errors.email"
                                        required
                                        autocomplete="username"
                                    />
                                    <InputError class="mt-2" :message="profileForm.errors.email" />
                                </div>

                                <div v-if="page.props.auth.user.email_verified_at" class="flex items-center gap-4">
                                    <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Tu email está verificado.
                                    </p>
                                </div>

                                <div v-else>
                                    <p class="text-sm text-gray-800 dark:text-gray-200">
                                        Tu dirección de correo electrónico no está verificada.
                                        <Link
                                            :href="route('verification.send')"
                                            method="post"
                                            as="button"
                                            class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            Haz clic aquí para reenviar el correo de verificación.
                                        </Link>
                                    </p>
                                </div>

                                <div class="flex items-center gap-4">
                                    <PrimaryButton type="submit" :disabled="profileForm.processing">
                                        {{ profileForm.processing ? 'Guardando...' : 'Guardar' }}
                                    </PrimaryButton>
                                    <Transition
                                        enter-active-class="transition ease-in-out"
                                        enter-from-class="opacity-0"
                                        leave-active-class="transition ease-in-out"
                                        leave-to-class="opacity-0"
                                    >
                                        <p v-if="profileForm.recentlySuccessful" class="text-sm text-green-600 dark:text-green-400 font-medium">
                                            ✓ Guardado exitosamente
                                        </p>
                                    </Transition>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>

                <!-- Actualizar Contraseña -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section>
                            <header>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                                    Actualizar Contraseña
                                </h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Asegúrate de usar una contraseña larga y aleatoria para mantener tu cuenta segura.
                                </p>
                            </header>

                            <form @submit.prevent="submitPassword" class="mt-6 space-y-6">
                                <div>
                                    <InputLabel for="current_password" value="Contraseña Actual" />
                                    <TextInput
                                        id="current_password"
                                        ref="currentPasswordInput"
                                        v-model="passwordForm.current_password"
                                        type="password"
                                        class="mt-1 block w-full"
                                        :error="passwordForm.errors.current_password"
                                        autocomplete="current-password"
                                    />
                                    <InputError :message="passwordForm.errors.current_password" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="password" value="Nueva Contraseña" />
                                    <TextInput
                                        id="password"
                                        ref="passwordInput"
                                        v-model="passwordForm.password"
                                        type="password"
                                        class="mt-1 block w-full"
                                        :error="passwordForm.errors.password"
                                        autocomplete="new-password"
                                    />
                                    <InputError :message="passwordForm.errors.password" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="password_confirmation" value="Confirmar Contraseña" />
                                    <TextInput
                                        id="password_confirmation"
                                        v-model="passwordForm.password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full"
                                        :error="passwordForm.errors.password_confirmation"
                                        autocomplete="new-password"
                                    />
                                    <InputError :message="passwordForm.errors.password_confirmation" class="mt-2" />
                                </div>

                                <div class="flex items-center gap-4">
                                    <PrimaryButton type="submit" :disabled="passwordForm.processing">
                                        {{ passwordForm.processing ? 'Guardando...' : 'Guardar' }}
                                    </PrimaryButton>
                                    <Transition
                                        enter-active-class="transition ease-in-out"
                                        enter-from-class="opacity-0"
                                        leave-active-class="transition ease-in-out"
                                        leave-to-class="opacity-0"
                                    >
                                        <p v-if="passwordForm.recentlySuccessful" class="text-sm text-green-600 dark:text-green-400 font-medium">
                                            ✓ Contraseña actualizada exitosamente
                                        </p>
                                    </Transition>
                                </div>
                            </form>
                        </section>
                    </div>
                </div>

                <!-- Eliminar Cuenta -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section>
                            <header>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                                    Eliminar Cuenta
                                </h2>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Antes de eliminar tu cuenta, descarga cualquier dato o información que desees conservar.
                                </p>
                            </header>

                            <DangerButton
                                class="mt-6"
                                @click="confirmUserDeletion"
                            >
                                Eliminar Cuenta
                            </DangerButton>

                            <!-- Modal de Confirmación -->
                            <Modal :show="confirmingUserDeletion" @close="closeModal">
                                <div class="p-6">
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                                        ¿Estás seguro de que quieres eliminar tu cuenta?
                                    </h2>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Por favor, ingresa tu contraseña para confirmar que deseas eliminar permanentemente tu cuenta.
                                    </p>
                                    <div class="mt-6">
                                        <InputLabel for="password" value="Contraseña" class="sr-only" />
                                        <TextInput
                                            id="delete_password"
                                            ref="passwordInput"
                                            v-model="deleteForm.password"
                                            type="password"
                                            class="mt-1 block w-full"
                                            :error="deleteForm.errors.password"
                                            placeholder="Contraseña"
                                            @keyup.enter="deleteUser"
                                        />
                                        <InputError :message="deleteForm.errors.password" class="mt-2" />
                                    </div>
                                    <div class="mt-6 flex justify-end">
                                        <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                                        <DangerButton
                                            class="ml-3"
                                            :class="{ 'opacity-25': deleteForm.processing }"
                                            :disabled="deleteForm.processing"
                                            @click="deleteUser"
                                        >
                                            Eliminar Cuenta
                                        </DangerButton>
                                    </div>
                                </div>
                            </Modal>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useForm, Link, usePage, router } from '@inertiajs/vue3';
import AppLayout from '@/Pages/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import Alert from '@/Components/Alert.vue';

const page = usePage();
const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
});

// Estado para controlar la visibilidad de los mensajes flash
const showFlashMessage = ref(!!page.props.flash.message);
const showFlashError = ref(!!page.props.flash.error);

// Observar cambios en los mensajes flash
watch(() => page.props.flash.message, (newValue) => {
    showFlashMessage.value = !!newValue;
    if (newValue) {
        // Ocultar el mensaje después de 5 segundos
        setTimeout(() => {
            showFlashMessage.value = false;
        }, 5000);
    }
});

watch(() => page.props.flash.error, (newValue) => {
    showFlashError.value = !!newValue;
    if (newValue) {
        // Ocultar el error después de 7 segundos
        setTimeout(() => {
            showFlashError.value = false;
        }, 7000);
    }
});

const profileForm = useForm({
    name: page.props.auth.user.name,
    email: page.props.auth.user.email,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const deleteForm = useForm({
    password: '',
});

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const submitProfileInformation = () => {
    profileForm.patch(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // El mensaje de éxito se mostrará desde flash.message
        },
        onError: (errors) => {
            // Los errores se mostrarán automáticamente en los campos
            console.log('Errores de validación:', errors);
        },
    });
};

const submitPassword = () => {
    passwordForm.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
        onError: () => {
            if (passwordForm.errors.password) {
                passwordInput.value.focus();
            }
            if (passwordForm.errors.current_password) {
                currentPasswordInput.value.focus();
            }
        },
    });
};

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    setTimeout(() => passwordInput.value?.focus(), 250);
};

const deleteUser = () => {
    deleteForm.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    deleteForm.reset();
};
</script>

