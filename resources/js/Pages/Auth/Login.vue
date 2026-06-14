<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
    canResetPassword: Boolean,
    status: String,
})

const form = useForm({
    email: '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    })
}

</script>

<template>
    <Head title="Login" />
    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
        {{ status }}
    </div>
    <div class="h-screen overflow-hidden bg-slate-950">
        <div class="grid h-full lg:grid-cols-[1.15fr_0.85fr]">

            <!-- LEFT -->
            <div
                class="relative hidden lg:flex items-center px-16 xl:px-24"
            >
                <div class="max-w-xl">

                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-500"
                        >
                            <i class="fa-solid fa-comments text-xl text-white"></i>
                        </div>

                        <div>
                            <h1 class="text-2xl font-bold text-white">
                                OmniInbox
                            </h1>

                            <p class="text-sm text-slate-400">
                                Unified Customer Messaging
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-8 inline-flex items-center gap-3 rounded-full border border-blue-500/20 bg-blue-500/10 px-4 py-2 text-sm text-blue-300"
                    >
                        <i class="fa-brands fa-facebook-messenger"></i>
                        Facebook

                        <i class="fa-brands fa-whatsapp"></i>
                        WhatsApp

                       <i class="fa-solid fa-globe"></i>

                        Website Chat
                    </div>

                    <h2
                        class="mt-8 text-5xl font-black leading-tight text-white xl:text-6xl"
                    >
                        One Inbox.

                        <span
                            class="block bg-gradient-to-r from-cyan-400 to-blue-500 bg-clip-text text-transparent"
                        >
                            Every conversation.
                        </span>
                    </h2>

                    <p
                        class="mt-6 max-w-lg text-base leading-8 text-slate-300"
                    >
                        Connect all customer messages in one place. Respond faster, collaborate better and never miss a lead.
                    </p>

                    <div class="mt-8 space-y-3">

                        <div class="flex items-center gap-3">
                            <span class="material-symbols-rounded text-green-400">
                                check_circle
                            </span>

                            <span class="text-slate-200">
                                Unified Inbox
                            </span>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="material-symbols-rounded text-green-400">
                                check_circle
                            </span>

                            <span class="text-slate-200">
                                Team Collaboration
                            </span>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="material-symbols-rounded text-green-400">
                                check_circle
                            </span>

                            <span class="text-slate-200">
                                Real-Time Messaging
                            </span>
                        </div>

                    </div>

                    <div class="mt-10 flex gap-12">

                        <div>
                            <div class="text-3xl font-bold text-white">
                                24K+
                            </div>

                            <div class="text-sm text-slate-400">
                                Messages
                            </div>
                        </div>

                        <div>
                            <div class="text-3xl font-bold text-white">
                                150+
                            </div>

                            <div class="text-sm text-slate-400">
                                Businesses
                            </div>
                        </div>

                        <div>
                            <div class="text-3xl font-bold text-white">
                                99.9%
                            </div>

                            <div class="text-sm text-slate-400">
                                Uptime
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <!-- RIGHT -->
            <div
                class="flex items-center justify-center px-6 lg:px-10"
            >
                <div
                    class="w-full max-w-md rounded-3xl bg-white p-10 shadow-[0_30px_80px_rgba(0,0,0,0.25)]"
                >

                    <div class="text-center lg:hidden">

                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-r from-blue-600 to-cyan-500"
                        >
                            <i class="fa-solid fa-comments text-xl text-white"></i>
                        </div>

                        <h1
                            class="mt-4 text-2xl font-bold text-slate-900"
                        >
                            OmniInbox
                        </h1>

                    </div>

                    <h2
                        class="mt-6 text-center text-3xl font-bold text-slate-900"
                    >
                        Welcome Back
                    </h2>

                    <p
                        class="mt-2 text-center text-slate-500"
                    >
                        Sign in to continue
                    </p>

                    <div
                        v-if="status"
                        class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-700"
                    >
                        {{ status }}
                    </div>

                    <div
                        v-if="form.errors.email"
                        class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-600"
                    >
                        {{ form.errors.email }}
                    </div>

                    <form
                        class="mt-6 space-y-5"
                        @submit.prevent="submit"
                    >

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Email
                            </label>

                            <input
                                v-model="form.email"
                                type="email"
                                autocomplete="username"
                                required
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none"
                                placeholder="admin@example.com"
                            />
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Password
                            </label>

                            <input
                                v-model="form.password"
                                type="password"
                                autocomplete="current-password"
                                required
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none"
                                placeholder="••••••••"
                            />
                        </div>

                        <div
                            class="flex items-center justify-between"
                        >

                            <label
                                class="flex items-center gap-2 text-sm text-slate-600"
                            >
                                <input
                                    v-model="form.remember"
                                    type="checkbox"
                                />

                                Remember me
                            </label>

                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="text-sm text-blue-600 hover:text-blue-700 hidden"
                            >
                                Forgot password?
                            </Link>

                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            :class="{ 'opacity-25': form.processing }"
                            class="w-full rounded-xl bg-gradient-to-r from-blue-600 to-cyan-500 py-3 font-semibold text-white transition hover:opacity-90 disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Signing In...'
                                    : 'Sign In'
                            }}
                        </button>

                    </form>
                    <div
                        class="mt-8 flex justify-center gap-5 text-xs text-slate-400"
                    >
                        <span class="flex items-center gap-2 text-slate-500">
                            <i class="fa-solid fa-lock text-emerald-500"></i>
                            Secure
                        </span>

                        <span class="flex items-center gap-2 text-slate-500">
                            <i class="fa-solid fa-bolt text-amber-400"></i>
                            Fast
                        </span>

                        <span class="flex items-center gap-2 text-slate-500">
                            <i class="fa-solid fa-cloud text-sky-500"></i>
                            Cloud
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>