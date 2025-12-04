import AdminV2Layout from '@/Layouts/AdminV2Layout';
import { Head, router } from '@inertiajs/react';
import { Student } from '@/Types';
import { route } from 'ziggy-js';
import Button from '@/Components/Button';

interface PendingStudentsProps {
    students: Student[];
}

export default function Pending({ students }: PendingStudentsProps) {
    const handleApprove = (studentId: number) => {
        if (confirm('この生徒を承認しますか？')) {
            router.post(route('admin.students.approve', studentId));
        }
    };

    const handleReject = (studentId: number) => {
        if (confirm('この申請を却下しますか？')) {
            router.post(route('admin.students.reject', studentId));
        }
    };

    const getStatusBadge = (status: string) => {
        const badges = {
            pending: 'bg-yellow-100 text-yellow-800',
            approved: 'bg-green-100 text-green-800',
            rejected: 'bg-red-100 text-red-800',
        };
        return badges[status as keyof typeof badges] || 'bg-gray-100 text-gray-800';
    };

    return (
        <AdminV2Layout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    申請待ち生徒一覧
                </h2>
            }
        >
            <Head title="申請待ち生徒一覧" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="mb-4">
                                <a
                                    href={route('admin.students.index')}
                                    className="text-blue-600 hover:text-blue-800 underline"
                                >
                                    ← 全生徒一覧へ
                                </a>
                            </div>

                            {students.length > 0 ? (
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Discord名
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    メールアドレス
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    申請日時
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    操作
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {students.map((student) => (
                                                <tr key={student.id}>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {student.discord_name}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {student.email}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {new Date(student.created_at).toLocaleString('ja-JP')}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <div className="flex space-x-2">
                                                            <Button
                                                                onClick={() => handleApprove(student.id)}
                                                                variant="primary"
                                                                className="bg-green-600 hover:bg-green-700"
                                                            >
                                                                承認
                                                            </Button>
                                                            <Button
                                                                onClick={() => handleReject(student.id)}
                                                                variant="secondary"
                                                                className="bg-red-600 hover:bg-red-700 text-white"
                                                            >
                                                                却下
                                                            </Button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="text-center py-8 text-gray-500">
                                    申請待ちの生徒はありません
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AdminV2Layout>
    );
}

