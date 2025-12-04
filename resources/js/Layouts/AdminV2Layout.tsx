import { Head, Link } from '@inertiajs/react';
import { PropsWithChildren, ReactNode } from 'react';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';

export default function AdminV2Layout({
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
                                <span className="text-xl font-bold text-gray-800">
                                    ProfileGen - 管理エリア
                                </span>
                            </div>

                            <div className="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <Link
                                    href={route('admin.consultations.index')}
                                    className="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out"
                                >
                                    相談履歴管理
                                </Link>
                                <Link
                                    href={route('admin.students.index')}
                                    className="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out"
                                >
                                    生徒管理
                                </Link>
                                <Link
                                    href={route('articles.index')}
                                    className="inline-flex items-center px-1 pt-1 text-sm font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition duration-150 ease-in-out"
                                >
                                    ナレッジ管理
                                </Link>
                            </div>
                        </div>

                        <div className="hidden sm:ms-6 sm:flex sm:items-center">
                            <form method="POST" action={route('admin.logout')}>
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

