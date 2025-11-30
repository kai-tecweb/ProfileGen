import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/Types';
import { Client } from '@/Types';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';

interface ClientsIndexProps extends PageProps {
    clients: {
        data: Client[];
        current_page: number;
        last_page: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}

export default function Index({ clients }: ClientsIndexProps) {
    const handleDelete = (id: number, name: string) => {
        if (confirm(`「${name}」を削除してもよろしいですか？関連する提案もすべて削除されます。`)) {
            router.delete(route('clients.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    クライアント管理
                </h2>
            }
        >
            <Head title="クライアント管理" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="flex justify-between items-center mb-6">
                                <h3 className="text-lg font-semibold text-gray-900">クライアント一覧</h3>
                                <Link href={route('clients.create')}>
                                    <Button>新規登録</Button>
                                </Link>
                            </div>

                            {clients.data.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    ID
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    名前
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    会社名
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    作成日時
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    操作
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {clients.data.map((client) => (
                                                <tr key={client.id}>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {client.id}
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-900">
                                                        <Link
                                                            href={route('clients.show', client.id)}
                                                            className="text-blue-600 hover:text-blue-900"
                                                        >
                                                            {client.name}
                                                        </Link>
                                                    </td>
                                                    <td className="px-6 py-4 text-sm text-gray-500">
                                                        {client.company || '-'}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {new Date(client.created_at).toLocaleString('ja-JP')}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <Link
                                                            href={route('clients.edit', client.id)}
                                                            className="text-blue-600 hover:text-blue-900 mr-4"
                                                        >
                                                            編集
                                                        </Link>
                                                        <button
                                                            onClick={() => handleDelete(client.id, client.name)}
                                                            className="text-red-600 hover:text-red-900"
                                                        >
                                                            削除
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>

                                    {/* ページネーション */}
                                    {clients.last_page > 1 && (
                                        <div className="mt-4 flex justify-center">
                                            <nav className="flex space-x-2">
                                                {clients.links.map((link, index) => (
                                                    <button
                                                        key={index}
                                                        onClick={() => {
                                                            if (link.url) {
                                                                router.visit(link.url);
                                                            }
                                                        }}
                                                        disabled={!link.url || link.active}
                                                        className={`px-3 py-2 rounded-md text-sm font-medium ${
                                                            link.active
                                                                ? 'bg-blue-600 text-white'
                                                                : link.url
                                                                ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                        }`}
                                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                                    />
                                                ))}
                                            </nav>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div className="text-center py-12">
                                    <p className="text-gray-500 mb-4">クライアントが登録されていません</p>
                                    <Link href={route('clients.create')}>
                                        <Button>最初のクライアントを登録</Button>
                                    </Link>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

