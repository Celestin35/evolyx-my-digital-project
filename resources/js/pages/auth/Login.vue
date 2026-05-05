<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { LogIn } from 'lucide-vue-next';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Connexion"
        description="Reprends ton suivi sportif et nutritionnel."
    >
        <Head title="Connexion" />

        <div
            v-if="status"
            class="mb-5 rounded-lg border border-green-500/20 bg-green-500/10 px-4 py-3 text-center text-sm font-medium text-green-700"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="space-y-6"
        >
            <div class="space-y-5">
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                        class="h-11 border-evo-black/15 bg-white/80"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between gap-3">
                        <Label for="password">Mot de passe</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm font-medium text-evo-black/60 hover:text-evo-black"
                            :tabindex="5"
                        >
                            Mot de passe oublié ?
                        </TextLink>
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="h-11 border-evo-black/15 bg-white/80"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center gap-3 text-evo-black/70">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Rester connecté</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="h-11 w-full bg-evo-black text-evo-white hover:bg-evo-black/90"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    <LogIn v-else class="size-4" />
                    Se connecter
                </Button>
            </div>

            <div
                class="text-center text-sm text-evo-black/60"
                v-if="canRegister"
            >
                Pas encore de compte ?
                <TextLink :href="register()" class="font-medium text-evo-black underline underline-offset-4" :tabindex="5">
                    Créer un compte
                </TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
