import { Head, Link } from '@inertiajs/react';
import { PropsWithChildren, ReactNode } from 'react';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';

export default function StudentLayout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    return (
        <div className="min-h-screen bg-gray-100">
            <nav className="border-b border-gray-100 bg-white">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-16 justify-between">
                        <div className="flex">
                            <div className="flex shrink-0 items-center">
                                <Link href="/">
                                    <span className="text-xl font-bold text-gray-800">
                                        ProfileGen - 学生エリア
                                    </span>
                                </Link>
                            </div>

                            <div className="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <Link
                                    href={route('student.consultations.index')}
                                    className="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out"
                                >
                                    相談チャット
                                </Link>
                            </div>
                        </div>

                        <div className="hidden sm:ms-6 sm:flex sm:items-center">
                            <form method="POST" action={route('student.logout')}>
                                <Button type="submit" variant="secondary">
                                    ログアウト
                                </Button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="bg-white shadow">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )}

            <main>{children}</main>
        </div>
    );
}

