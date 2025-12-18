<template>
    <AppLayout title="Perfil">
        <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition-colors duration-300">
            <!-- Hero Header con Avatar -->
            <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 dark:from-blue-800 dark:via-purple-800 dark:to-pink-800">
                <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <!-- Avatar y Información del Usuario -->
                        <div class="flex items-center gap-6">
                            <!-- Avatar con animación -->
                            <div class="relative group">
                                <div class="w-24 h-24 rounded-2xl bg-white dark:bg-gray-800 flex items-center justify-center text-3xl font-bold text-gray-700 dark:text-gray-200 shadow-2xl transform transition-all duration-300 group-hover:scale-110 group-hover:shadow-3xl ring-4 ring-white dark:ring-gray-700">
                                    {{ userInitials }}
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-8 h-8 bg-green-500 rounded-full border-4 border-white dark:border-gray-800 flex items-center justify-center animate-pulse">
                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            
                            <div class="flex-1">
                                <h1 class="text-4xl font-bold text-white mb-2 tracking-tight">
                                    {{ page.props.auth.user.name }}
                                </h1>
                                <p class="text-lg text-blue-100 dark:text-blue-200 mb-3">
                                    {{ page.props.auth.user.email }}
                                </p>
                                <div class="flex items-center gap-4">
                                    <div v-if="page.props.auth.user.email_verified_at" class="flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm text-white">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Email Verificado
                                    </div>
                                    <div v-else class="flex items-center gap-2 px-3 py-1 bg-yellow-500/20 backdrop-blur-sm rounded-full text-sm text-yellow-100">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        Email No Verificado
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <!-- Mensajes Flash -->
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-2"
                >
                    <div v-if="showFlashMessage && page.props.flash.message" class="mb-6">
                        <Alert
                            type="success"
                            dismissible
                            @dismiss="showFlashMessage = false"
                        >
                            {{ page.props.flash.message }}
                        </Alert>
                    </div>
                </Transition>
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 translate-y-2"
                >
                    <div v-if="showFlashError && page.props.flash.error" class="mb-6">
                        <Alert
                            type="error"
                            dismissible
                            @dismiss="showFlashError = false"
                        >
                            {{ page.props.flash.error }}
                        </Alert>
                    </div>
                </Transition>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Columna Principal -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Información del Perfil -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
                            <div class="px-6 py-5 bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                            Información del Perfil
                                        </h2>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Actualiza tu información personal
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <form @submit.prevent="submitProfileInformation" class="p-6 space-y-6">
                                <div class="space-y-2">
                                    <InputLabel for="name" value="Nombre Completo" class="text-gray-700 dark:text-gray-300 font-medium" />
                                    <TextInput
                                        id="name"
                                        type="text"
                                        class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                                        v-model="profileForm.name"
                                        :error="profileForm.errors.name"
                                        placeholder="Ingresa tu nombre completo"
                                        required
                                        autofocus
                                        autocomplete="name"
                                    />
                                    <InputError class="mt-2" :message="profileForm.errors.name" />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel for="email" value="Correo Electrónico" class="text-gray-700 dark:text-gray-300 font-medium" />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                                        v-model="profileForm.email"
                                        :error="profileForm.errors.email"
                                        placeholder="tu@email.com"
                                        required
                                        autocomplete="username"
                                    />
                                    <InputError class="mt-2" :message="profileForm.errors.email" />
                                </div>

                                <div v-if="!page.props.auth.user.email_verified_at" class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl">
                                    <p class="text-sm text-yellow-800 dark:text-yellow-200 mb-2">
                                        Tu dirección de correo electrónico no está verificada.
                                    </p>
                                    <Link
                                        :href="route('verification.send')"
                                        method="post"
                                        as="button"
                                        class="text-sm font-medium text-yellow-700 dark:text-yellow-300 hover:text-yellow-900 dark:hover:text-yellow-100 underline transition-colors duration-200"
                                    >
                                        Reenviar correo de verificación
                                    </Link>
                                </div>

                                <div class="flex items-center gap-4 pt-4">
                                    <PrimaryButton 
                                        type="submit" 
                                        :disabled="profileForm.processing"
                                        class="px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300"
                                    >
                                        <span v-if="!profileForm.processing" class="flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Guardar Cambios
                                        </span>
                                        <span v-else class="flex items-center gap-2">
                                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Guardando...
                                        </span>
                                    </PrimaryButton>
                                    <Transition
                                        enter-active-class="transition ease-in-out duration-300"
                                        enter-from-class="opacity-0 scale-95"
                                        enter-to-class="opacity-100 scale-100"
                                        leave-active-class="transition ease-in-out duration-200"
                                        leave-from-class="opacity-100 scale-100"
                                        leave-to-class="opacity-0 scale-95"
                                    >
                                        <div v-if="profileForm.recentlySuccessful" class="flex items-center gap-2 px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-xl font-medium text-sm">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            Guardado exitosamente
                                        </div>
                                    </Transition>
                                </div>
                            </form>
                        </div>

                        <!-- Actualizar Contraseña -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 dark:border-gray-700">
                            <div class="px-6 py-5 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                            Seguridad
                                        </h2>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Cambia tu contraseña
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <form @submit.prevent="submitPassword" class="p-6 space-y-6">
                                <div class="space-y-2">
                                    <InputLabel for="current_password" value="Contraseña Actual" class="text-gray-700 dark:text-gray-300 font-medium" />
                                    <TextInput
                                        id="current_password"
                                        ref="currentPasswordInput"
                                        v-model="passwordForm.current_password"
                                        type="password"
                                        class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                                        :error="passwordForm.errors.current_password"
                                        placeholder="Ingresa tu contraseña actual"
                                        autocomplete="current-password"
                                    />
                                    <InputError :message="passwordForm.errors.current_password" class="mt-2" />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel for="password" value="Nueva Contraseña" class="text-gray-700 dark:text-gray-300 font-medium" />
                                    <TextInput
                                        id="password"
                                        ref="passwordInput"
                                        v-model="passwordForm.password"
                                        type="password"
                                        class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                                        :error="passwordForm.errors.password"
                                        placeholder="Ingresa tu nueva contraseña (mínimo 8 caracteres)"
                                        autocomplete="new-password"
                                    />
                                    <InputError :message="passwordForm.errors.password" class="mt-2" />
                                </div>

                                <div class="space-y-2">
                                    <InputLabel for="password_confirmation" value="Confirmar Contraseña" class="text-gray-700 dark:text-gray-300 font-medium" />
                                    <TextInput
                                        id="password_confirmation"
                                        v-model="passwordForm.password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-green-500 focus:border-green-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                                        :error="passwordForm.errors.password_confirmation"
                                        placeholder="Confirma tu nueva contraseña"
                                        autocomplete="new-password"
                                    />
                                    <InputError :message="passwordForm.errors.password_confirmation" class="mt-2" />
                                </div>

                                <div class="flex items-center gap-4 pt-4">
                                    <PrimaryButton 
                                        type="submit" 
                                        :disabled="passwordForm.processing"
                                        class="px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600"
                                    >
                                        <span v-if="!passwordForm.processing" class="flex items-center gap-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Actualizar Contraseña
                                        </span>
                                        <span v-else class="flex items-center gap-2">
                                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Guardando...
                                        </span>
                                    </PrimaryButton>
                                    <Transition
                                        enter-active-class="transition ease-in-out duration-300"
                                        enter-from-class="opacity-0 scale-95"
                                        enter-to-class="opacity-100 scale-100"
                                        leave-active-class="transition ease-in-out duration-200"
                                        leave-from-class="opacity-100 scale-100"
                                        leave-to-class="opacity-0 scale-95"
                                    >
                                        <div v-if="passwordForm.recentlySuccessful" class="flex items-center gap-2 px-4 py-2 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-xl font-medium text-sm">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            Contraseña actualizada
                                        </div>
                                    </Transition>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Eliminar Cuenta -->
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border border-red-100 dark:border-red-900/30">
                            <div class="px-6 py-5 bg-gradient-to-r from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20 border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-pink-500 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                            Zona de Peligro
                                        </h2>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Acciones irreversibles
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6">
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                                    Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Antes de eliminar tu cuenta, descarga cualquier dato o información que desees conservar.
                                </p>

                                <DangerButton
                                    class="w-full px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300"
                                    @click="confirmUserDeletion"
                                >
                                    <span class="flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Eliminar Cuenta
                                    </span>
                                </DangerButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Confirmación -->
            <Modal :show="confirmingUserDeletion" @close="closeModal">
                <div class="p-6">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                ¿Eliminar tu cuenta?
                            </h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Esta acción no se puede deshacer
                            </p>
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                        Una vez que se elimine tu cuenta, todos sus recursos y datos se eliminarán permanentemente. Por favor, ingresa tu contraseña para confirmar que deseas eliminar permanentemente tu cuenta.
                    </p>
                    
                    <div class="mb-6">
                        <InputLabel for="delete_password" value="Contraseña" class="text-gray-700 dark:text-gray-300 font-medium mb-2" />
                        <TextInput
                            id="delete_password"
                            ref="passwordInput"
                            v-model="deleteForm.password"
                            type="password"
                            class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                            :error="deleteForm.errors.password"
                            placeholder="Ingresa tu contraseña para confirmar"
                            @keyup.enter="deleteUser"
                        />
                        <InputError :message="deleteForm.errors.password" class="mt-2" />
                    </div>
                    
                    <div class="flex justify-end gap-3">
                        <SecondaryButton 
                            @click="closeModal"
                            class="px-6 py-3 rounded-xl font-semibold"
                        >
                            Cancelar
                        </SecondaryButton>
                        <DangerButton
                            class="px-6 py-3 rounded-xl font-semibold"
                            :class="{ 'opacity-25': deleteForm.processing }"
                            :disabled="deleteForm.processing"
                            @click="deleteUser"
                        >
                            <span v-if="!deleteForm.processing" class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Eliminar Cuenta
                            </span>
                            <span v-else class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Eliminando...
                            </span>
                        </DangerButton>
                    </div>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, Link, usePage } from '@inertiajs/vue3';
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

// Computed para iniciales del usuario
const userInitials = computed(() => {
    const name = page.props.auth.user?.name || '';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
});

// Estado para controlar la visibilidad de los mensajes flash
const showFlashMessage = ref(!!page.props.flash.message);
const showFlashError = ref(!!page.props.flash.error);

// Observar cambios en los mensajes flash
watch(() => page.props.flash.message, (newValue) => {
    showFlashMessage.value = !!newValue;
    if (newValue) {
        setTimeout(() => {
            showFlashMessage.value = false;
        }, 5000);
    }
});

watch(() => page.props.flash.error, (newValue) => {
    showFlashError.value = !!newValue;
    if (newValue) {
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

<style scoped>
.bg-grid-pattern {
    background-image: 
        linear-gradient(to right, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
    background-size: 20px 20px;
}
</style>
