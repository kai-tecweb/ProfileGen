import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import Button from '@/Components/Button';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import { Link } from '@inertiajs/react';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        discord_name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('student.register'));
    };

    return (
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <Head title="生徒登録申請" />

            <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                <h2 className="text-2xl font-bold text-gray-800 mb-6 text-center">
                    生徒登録申請
                </h2>

                <p className="text-sm text-gray-600 mb-6 text-center">
                    管理者の承認後、ログインできるようになります。
                </p>

                <form onSubmit={submit}>
                    <div className="mt-4">
                        <InputLabel htmlFor="discord_name" value="Discord名" />
                        <TextInput
                            id="discord_name"
                            type="text"
                            name="discord_name"
                            value={data.discord_name}
                            className="mt-1 block w-full"
                            autoComplete="username"
                            isFocused={true}
                            onChange={(e) => setData('discord_name', e.target.value)}
                            placeholder="例: username#1234"
                        />
                        <InputError message={errors.discord_name} className="mt-2" />
                    </div>

                    <div className="mt-4">
                        <InputLabel htmlFor="email" value="メールアドレス" />
                        <TextInput
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="mt-1 block w-full"
                            autoComplete="email"
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        <InputError message={errors.email} className="mt-2" />
                    </div>

                    <div className="mt-4">
                        <InputLabel htmlFor="password" value="パスワード" />
                        <TextInput
                            id="password"
                            type="password"
                            name="password"
                            value={data.password}
                            className="mt-1 block w-full"
                            autoComplete="new-password"
                            onChange={(e) => setData('password', e.target.value)}
                        />
                        <InputError message={errors.password} className="mt-2" />
                        <p className="mt-1 text-sm text-gray-500">8文字以上で入力してください</p>
                    </div>

                    <div className="mt-4">
                        <InputLabel htmlFor="password_confirmation" value="パスワード（確認）" />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            value={data.password_confirmation}
                            className="mt-1 block w-full"
                            autoComplete="new-password"
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                        />
                        <InputError message={errors.password_confirmation} className="mt-2" />
                    </div>

                    <div className="mt-6 flex items-center justify-between">
                        <Link
                            href={route('student.login')}
                            className="text-sm text-gray-600 hover:text-gray-900 underline"
                        >
                            既にアカウントをお持ちの方はこちら
                        </Link>
                        <Button type="submit" disabled={processing}>
                            {processing ? '申請中...' : '申請する'}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    );
}

