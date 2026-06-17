<template>
    <div class="p-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto">
            <!-- Tabs -->
            <div class="mb-6">
                <div class="inline-flex bg-white p-1 rounded-2xl shadow-sm border">
                    <button
                        @click="activeTab = 'new'"
                        :class="[
                            'px-6 py-3 rounded-xl text-sm font-medium transition-all',
                            activeTab === 'new'
                                ? 'bg-blue-600 text-white shadow'
                                : 'text-slate-600 hover:bg-slate-100'
                        ]"
                    >
                        Send as New
                    </button>

                    <button
                        @click="
                            activeTab = 'reply';
                            loadConversations();
                        "
                        :class="[
                            'px-6 py-3 rounded-xl text-sm font-medium transition-all',
                            activeTab === 'reply'
                                ? 'bg-blue-600 text-white shadow'
                                : 'text-slate-600 hover:bg-slate-100'
                        ]"
                    >
                        Old Reply
                    </button>
                </div>
            </div>

            <!-- SEND AS NEW -->
            <div
                v-if="activeTab === 'new'"
                class="space-y-6"
            >

                <!-- Contact Information -->
                <div class="bg-white rounded-3xl border shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-slate-800 mb-5">
                        Contact Information
                    </h2>

                    <div class="grid lg:grid-cols-3 md:grid-cols-2 gap-5">

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">
                                Name
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Enter name"
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">
                                Phone
                            </label>
                            <input
                                v-model="form.phone"
                                type="text"
                                placeholder="Enter phone"
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">
                                Platform
                            </label>

                            <select 
                                v-model="form.platform"
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none"
                            >
                                <option value="whatsapp">WhatsApp</option>
                                <option value="messenger">Messenger</option>
                                <option value="website">Website</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">
                                Email
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="Enter email"
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none"
                            >
                        </div>
                    </div>
                </div>

                <!-- Message -->
                <div class="bg-white rounded-3xl border shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-slate-800 mb-5">
                        Message
                    </h2>

                    <textarea
                        v-model="form.message"
                        rows="8"
                        placeholder="Write your message here..."
                        class="w-full rounded-2xl border border-slate-200 px-4 py-4 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none resize-none"
                    ></textarea>
                </div>

                <!-- Attachment -->
                <div class="bg-white rounded-3xl border shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-slate-800 mb-5">
                        Attachment
                    </h2>

                    <label
                        class="border-2 border-dashed border-slate-300 rounded-3xl p-10 flex flex-col items-center justify-center text-center cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-12 h-12 text-slate-400 mb-3"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M12 16V4m0 0l-4 4m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2"
                            />
                        </svg>

                        <h4 class="font-semibold text-slate-700">
                            Drag & Drop Files
                        </h4>

                        <p class="text-sm text-slate-500 mt-1">
                            or click to browse
                        </p>

                        <input
                            type="file"
                            class="hidden"
                            @change="form.attachment = $event.target.files[0]"
                        >
                    </label>
                </div>

                <!-- Button -->
                <div class="flex justify-end">
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="px-8 py-3 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition shadow-lg shadow-blue-200"
                    >
                        {{ form.processing ? 'Sending...' : 'Send Message' }}
                    </button>
                </div>
            </div>

            <!-- OLD REPLY -->
            <div
                v-if="activeTab === 'reply'"
                class="bg-white rounded-3xl border shadow-sm overflow-hidden"
            >
                <div class="flex h-[515px]">

                    <!-- Sidebar -->
                    <div class="w-96 border-r bg-slate-50 flex flex-col">

                        <div class="p-4 border-b bg-white">
                            <input
                                type="text"
                                placeholder="Search user..."
                                class="w-full h-11 px-4 rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none"
                            >
                        </div>

                        <div class="flex-1 overflow-y-auto">

                            <div
                                v-for="user in users"
                                :key="user.id"
                                @click="loadMessages(user.id)"
                                class="p-4 border-b hover:bg-white cursor-pointer transition"
                                :class="{
                                    'bg-white': selectedConversation?.id === user.id
                                }"
                            >
                                <div class="flex items-center gap-3">

                                    <div
                                        class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center font-semibold text-blue-600"
                                    >
                                        {{ user.contact?.name?.charAt(0) || '?' }}
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-medium text-slate-800">
                                            {{ user.contact?.name }}
                                        </h4>

                                        <div class="flex items-center justify-between mt-1">
                                            <p class="text-xs text-slate-500 capitalize">
                                                {{ user.contact?.platform }}
                                            </p>

                                            <p class="text-xs text-slate-400 whitespace-nowrap">
                                                {{ timeAgo(user.last_message_at) }}
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Chat Area -->
                    <div class="flex-1 flex flex-col">

                        <!-- Header -->
                        <div
                            class="px-6 py-4 border-b bg-white flex items-center justify-between"
                        >
                            <div>
                                <h3 class="font-semibold text-slate-800">
                                    {{ selectedConversation?.contact?.name || 'Select a conversation' }}
                                </h3>
                                <p class="text-sm text-green-600">
                                    Online
                                </p>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div
                            class="flex-1 overflow-y-auto p-6 bg-slate-100"
                        >

                            <div
                                v-if="!selectedConversation"
                                class="h-full flex items-center justify-center"
                            >
                                <div class="text-center">
                                    <h3 class="text-lg font-semibold text-slate-700">
                                        No conversation selected
                                    </h3>

                                    <p class="text-slate-500 mt-2">
                                        Select a user from the left sidebar
                                    </p>
                                </div>
                            </div>

                            <template v-else>

                                <div
                                    v-for="message in messages"
                                    :key="message.id"
                                    class="mb-4"
                                >

                                    <div
                                        :class="[
                                            'flex',
                                            message.sender === 'admin'
                                                ? 'justify-end'
                                                : 'justify-start'
                                        ]"
                                    >
                                        <div
                                            :class="[
                                                'px-5 py-3 rounded-2xl max-w-md',
                                                message.sender === 'admin'
                                                    ? 'bg-blue-600 text-white rounded-br-md'
                                                    : 'bg-white shadow-sm rounded-bl-md'
                                            ]"
                                        >
                                            {{ message.message }}
                                        </div>
                                    </div>

                                </div>

                            </template>

                        </div>

                        <!-- Reply Box -->
                        <div class="p-4 border-t bg-white">
                            <div class="flex gap-3 items-end">

                                <textarea
                                    rows="2"
                                    placeholder="Type your reply..."
                                    class="flex-1 rounded-2xl border border-slate-200 px-4 py-3 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none resize-none"
                                ></textarea>

                                <button
                                    class="px-6 py-3 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition"
                                >
                                    Send
                                </button>

                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
    import { ref } from 'vue'
    import axios from 'axios'
    import { useForm } from '@inertiajs/vue3'
    const users = ref([])
    const loaded = ref(false)
    const activeTab = ref('new')
    const selectedConversation = ref(null)
    const messages = ref([])
    const loadingMessages = ref(false)

    const loadConversations = async () => {
        if (loaded.value) return

        try {

            const response = await axios.get(
                route('chat.conversations')
            )

            users.value = response.data.data

            loaded.value = true

        } catch (error) {
            console.error(error)
        }
    }



    const form = useForm({
        name: '',
        phone: '',
        email: '',
        platform: 'website',
        message: '',
        attachment: null,
    })

    const submit = () => {
        form.post(route('chat.store'), {
            forceFormData: true,

            onSuccess: () => {
                form.reset()

                form.platform = 'website'
            }
        })
    }

    const loadMessages = async (conversationId) => {
        loadingMessages.value = true

        try {

            const response = await axios.get(
                route('chat.conversation', conversationId)
            )

            selectedConversation.value =
                response.data.conversation

            messages.value =
                response.data.conversation.messages || []

        } catch (error) {
            console.error(error)
        } finally {
            loadingMessages.value = false
        }
    }

    const timeAgo = (date) => {
        const now = new Date()
        const past = new Date(date)

        const seconds = Math.floor((now - past) / 1000)

        if (seconds < 60) {
            return 'Just now'
        }

        const minutes = Math.floor(seconds / 60)

        if (minutes < 60) {
            return `${minutes} min ago`
        }

        const hours = Math.floor(minutes / 60)

        if (hours < 24) {
            return `${hours} hour${hours > 1 ? 's' : ''} ago`
        }

        const days = Math.floor(hours / 24)

        if (days === 1) {
            return 'Yesterday'
        }

        if (days < 7) {
            return `${days} days ago`
        }

        const weeks = Math.floor(days / 7)

        if (weeks < 5) {
            return `${weeks} week${weeks > 1 ? 's' : ''} ago`
        }

        const months = Math.floor(days / 30)

        if (months < 12) {
            return `${months} month${months > 1 ? 's' : ''} ago`
        }

        const years = Math.floor(days / 365)

        return `${years} year${years > 1 ? 's' : ''} ago`
    }
</script>