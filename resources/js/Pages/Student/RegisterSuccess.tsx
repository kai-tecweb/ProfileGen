import { Head, Link } from '@inertiajs/react';
import Button from '@/Components/Button';

export default function RegisterSuccess() {
    return (
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <Head title="申請完了" />

            <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                <div className="text-center">
                    <div className="mb-4">
                        <svg
                            className="mx-auto h-12 w-12 text-green-500"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>
                    <h2 className="text-2xl font-bold text-gray-800 mb-4">
                        申請を受け付けました
                    </h2>
                    <p className="text-gray-600 mb-6">
                        管理者の承認をお待ちください。<br />
                        承認後、Discord名とパスワードでログインできます。
                    </p>
                    <Link href={route('student.login')}>
                        <Button>ログインページへ</Button>
                    </Link>
                </div>
            </div>
        </div>
    );
}

